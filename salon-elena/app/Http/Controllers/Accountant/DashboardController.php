<?php
// app/Http/Controllers/Accountant/DashboardController.php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\MaterialReceipt;
use App\Models\PaymentToSupplier;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\ClientContract;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Получить статистику для сайдбара
     */
    private function getSidebarStats()
    {
        $today = Carbon::today();
        
        // Выручка сегодня (оплаченные услуги)
        $todayRevenue = ClientContract::whereDate('created_at', $today)->sum('total_amount');
        
        // Неоплаченные услуги
        $unpaidServices = Appointment::where('status', 2)
            ->whereDoesntHave('clientContract')
            ->get();
        $pendingPayments = 0;
        foreach ($unpaidServices as $service) {
            $pendingPayments += $service->calculateTotalPrice();
        }
        
        // Критические материалы
        $criticalCount = Material::where('current_balance', '<=', DB::raw('min_stock'))->count();
        
        // Неоплаченные услуги (количество)
        $unpaidCount = $unpaidServices->count();
        
        return [
            'unpaidCount' => $unpaidCount,
            'criticalCount' => $criticalCount,
            'todayRevenue' => $todayRevenue,
            'pendingPayments' => $pendingPayments,
        ];
    }

    /**
     * Главная страница с уведомлениями
     */
    public function index()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();
        
        // Неоплаченные услуги (завершенные, но без чека)
        $unpaidServices = Appointment::with(['client', 'providedServices.service'])
            ->where('status', 2)
            ->whereDoesntHave('clientContract')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($appointment) {
                $totalPrice = $appointment->calculateTotalPrice();
                return [
                    'id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'formatted_date' => Carbon::parse($appointment->date)->format('d.m.Y H:i'),
                    'client_name' => $appointment->client->client_name,
                    'service_name' => $appointment->services_list,
                    'total_price' => $totalPrice,
                ];
            });
        
        // Критические материалы (остаток <= минимальному)
        $criticalMaterials = Material::where('current_balance', '<=', DB::raw('min_stock'))
            ->get()
            ->map(function($material) {
                return [
                    'id' => $material->material_id,
                    'name' => $material->name,
                    'current_balance' => $material->current_balance,
                    'unit' => $material->unit,
                    'min_stock' => $material->min_stock,
                ];
            });
        
        // Последние операции
        $recentPayments = ClientContract::with(['client', 'appointment'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function($contract) {
                return [
                    'id' => $contract->contract_id,
                    'date' => $contract->created_at,
                    'formatted_date' => Carbon::parse($contract->created_at)->format('d.m.Y H:i'),
                    'client_name' => $contract->client->client_name,
                    'amount' => $contract->total_amount,
                    'contract_number' => $contract->contract_number,
                ];
            });
        
        return Inertia::render('Accountant/Dashboard', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'stats' => [
                'unpaid_count' => $stats['unpaidCount'],
                'critical_count' => $stats['criticalCount'],
                'today_revenue' => $stats['todayRevenue'],
                'pending_payments' => $stats['pendingPayments'],
            ],
            'unpaidServices' => $unpaidServices,
            'criticalMaterials' => $criticalMaterials,
            'recentPayments' => $recentPayments,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница оплат услуг
     */
    public function payments()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();
        
        $appointments = Appointment::with(['client', 'providedServices.service', 'clientContract'])
            ->where('status', 2)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($appointment) {
                $totalPrice = $appointment->calculateTotalPrice();
                return [
                    'id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'formatted_date' => Carbon::parse($appointment->date)->format('d.m.Y H:i'),
                    'client_name' => $appointment->client->client_name,
                    'service_name' => $appointment->services_list,
                    'total_price' => $totalPrice,
                    'status' => $appointment->clientContract ? 'paid' : 'pending',
                    'contract_number' => $appointment->clientContract?->contract_number,
                    'payment_date' => $appointment->clientContract?->created_at ? Carbon::parse($appointment->clientContract->created_at)->format('d.m.Y H:i') : null,
                ];
            });
        
        return Inertia::render('Accountant/Payments', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'payments' => $appointments,
            'unpaidCount' => $stats['unpaidCount'],
            'criticalCount' => $stats['criticalCount'],
            'todayRevenue' => $stats['todayRevenue'],
            'pendingPayments' => $stats['pendingPayments'],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Принять оплату и создать чек
     */
    public function acceptPayment(Request $request, $id)
    {
        $user = Auth::guard('employee')->user();
        
        $appointment = Appointment::with(['providedServices.service', 'client'])
            ->findOrFail($id);
        
        if ($appointment->clientContract) {
            return response()->json(['error' => 'Оплата уже проведена'], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $totalPrice = $appointment->calculateTotalPrice();
            $contractNumber = 'INV-' . date('Ymd') . '-' . str_pad($appointment->appointment_id, 6, '0', STR_PAD_LEFT);
            
            $contract = ClientContract::create([
                'contract_date' => now(),
                'status' => 1,
                'contract_number' => $contractNumber,
                'total_amount' => $totalPrice,
                'signed_at' => now(),
                'employee_id' => $appointment->employee_id,
                'client_id' => $appointment->client_id,
                'appointment_id' => $appointment->appointment_id,
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Оплата принята',
                'contract_number' => $contractNumber,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Страница склада
     */
    public function warehouse()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();
        
        $materials = Material::orderBy('name')->get()
            ->map(function($material) {
                $status = 'normal';
                if ($material->current_balance <= $material->min_stock) {
                    $status = 'critical';
                } elseif ($material->current_balance <= $material->min_stock * 2) {
                    $status = 'low';
                }
                
                return [
                    'id' => $material->material_id,
                    'name' => $material->name,
                    'current_balance' => $material->current_balance,
                    'unit' => $material->unit,
                    'min_stock' => $material->min_stock,
                    'price_per_unit' => $material->price_per_unit,
                    'status' => $status,
                ];
            });
        
        $suppliers = Supplier::all();
        
        // Активные заказы
        $activeOrders = SupplierContract::where('status', 1)
            ->with(['supplier'])
            ->get()
            ->map(function($contract) {
                return [
                    'id' => $contract->contract_id,
                    'number' => $contract->number,
                    'date' => $contract->date,
                    'supplier_name' => $contract->supplier?->supplier_name,
                    'status' => $contract->status,
                    'valid_from' => $contract->valid_from,
                    'valid_to' => $contract->valid_to,
                ];
            });
        
        return Inertia::render('Accountant/Warehouse', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'materials' => $materials,
            'suppliers' => $suppliers,
            'activeOrders' => $activeOrders,
            'unpaidCount' => $stats['unpaidCount'],
            'criticalCount' => $stats['criticalCount'],
            'todayRevenue' => $stats['todayRevenue'],
            'pendingPayments' => $stats['pendingPayments'],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Создать заказ на закупку материалов
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'items' => 'required|array',
            'items.*.material_id' => 'required|exists:materials,material_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_per_unit' => 'required|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Создаем договор с поставщиком
            $contractNumber = 'SC-' . date('Ymd') . '-' . rand(100, 999);
            
            $contract = SupplierContract::create([
                'number' => $contractNumber,
                'date' => now(),
                'status' => 0,
                'valid_from' => now(),
                'valid_to' => Carbon::now()->addMonths(1),
                'supplier_id' => $request->supplier_id,
            ]);
            
            $totalAmount = 0;
            
            // Создаем записи о поступлении материалов
            foreach ($request->items as $item) {
                $material = Material::find($item['material_id']);
                $totalItemPrice = $item['quantity'] * $item['price_per_unit'];
                $totalAmount += $totalItemPrice;
                
                MaterialReceipt::create([
                    'quantity' => $item['quantity'],
                    'price' => $item['price_per_unit'],
                    'batch_number' => time(),
                    'expiry_date' => Carbon::now()->addYears(1)->timestamp,
                    'receipt_date' => now(),
                    'invoice_number' => 'INV-' . time(),
                    'status' => 0,
                    'material_id' => $item['material_id'],
                    'contract_id' => $contract->contract_id,
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Заказ создан и отправлен на утверждение',
                'order_id' => $contract->contract_id,
                'contract_number' => $contractNumber,
                'total_amount' => $totalAmount,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Подтвердить получение заказа (разгрузка материалов на склад)
     */
    public function receiveOrder($receiptId)
    {
        $receipt = MaterialReceipt::with(['material', 'contract'])
            ->findOrFail($receiptId);
        
        if ($receipt->status == 1) {
            return response()->json(['error' => 'Товар уже принят'], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Увеличиваем остаток материала
            $material = $receipt->material;
            $material->current_balance += $receipt->quantity;
            $material->save();
            
            // Обновляем статус поступления
            $receipt->status = 1;
            $receipt->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Товары приняты на склад',
                'material_name' => $material->name,
                'added_quantity' => $receipt->quantity,
                'new_balance' => $material->current_balance,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Страница зарплаты
     */
    public function salary()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();
        
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        $employees = Employee::whereIn('role', ['doctor', 'admin', 'director'])
            ->orderBy('role')
            ->orderBy('employee_name')
            ->get()
            ->map(function($employee) use ($currentMonth, $currentYear) {
                $salaryData = $employee->getSalaryForMonth($currentMonth, $currentYear);
                
                return [
                    'id' => $employee->employee_id,
                    'name' => $employee->employee_name,
                    'role' => $employee->role,
                    'hourly_rate' => $employee->hourly_rate ?? 0,
                    'hours_worked' => $salaryData->hours_worked,
                    'total_amount' => $salaryData->total_amount,
                    'is_paid' => $salaryData->is_paid,
                ];
            });
        
        $totalSalary = $employees->sum('total_amount');
        
        return Inertia::render('Accountant/Salary', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'employees' => $employees,
            'totalSalary' => $totalSalary,
            'currentMonth' => Carbon::now()->translatedFormat('F Y'),
            'currentMonthNumber' => $currentMonth,
            'currentYear' => $currentYear,
            'unpaidCount' => $stats['unpaidCount'],
            'criticalCount' => $stats['criticalCount'],
            'todayRevenue' => $stats['todayRevenue'],
            'pendingPayments' => $stats['pendingPayments'],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Рассчитать зарплату за выбранный месяц
     */
    public function calculateSalary(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ]);
        
        $month = $request->month;
        $year = $request->year;
        
        $employees = Employee::whereIn('role', ['doctor', 'admin', 'director'])
            ->orderBy('role')
            ->orderBy('employee_name')
            ->get()
            ->map(function($employee) use ($month, $year) {
                $salaryData = $employee->getSalaryForMonth($month, $year);
                
                return [
                    'id' => $employee->employee_id,
                    'name' => $employee->employee_name,
                    'role' => $employee->role,
                    'hourly_rate' => $employee->hourly_rate ?? 0,
                    'hours_worked' => $salaryData->hours_worked,
                    'total_amount' => $salaryData->total_amount,
                    'is_paid' => $salaryData->is_paid,
                ];
            });
        
        $totalSalary = $employees->sum('total_amount');
        
        return response()->json([
            'employees' => $employees,
            'totalSalary' => $totalSalary,
        ]);
    }
    
    /**
     * Сохранить зарплату и отметить как выплаченную
     */
    public function paySalary(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ]);
        
        $employee = Employee::findOrFail($request->employee_id);
        
        DB::beginTransaction();
        
        try {
            $salary = $employee->saveSalaryForMonth($request->month, $request->year);
            $salary->is_paid = true;
            $salary->payment_date = now();
            $salary->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Зарплата {$employee->employee_name} за " . Carbon::create($request->year, $request->month, 1)->translatedFormat('F Y') . " выплачена",
                'amount' => $salary->total_amount,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Выплатить зарплату всем сотрудникам за месяц
     */
    public function payAllSalaries(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ]);
        
        $employees = Employee::whereIn('role', ['doctor', 'admin', 'director'])->get();
        $paidCount = 0;
        $totalPaid = 0;
        
        DB::beginTransaction();
        
        try {
            foreach ($employees as $employee) {
                $salary = $employee->saveSalaryForMonth($request->month, $request->year);
                
                if (!$salary->is_paid) {
                    $salary->is_paid = true;
                    $salary->payment_date = now();
                    $salary->save();
                    $paidCount++;
                    $totalPaid += $salary->total_amount;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Выплачено зарплат: {$paidCount}, на сумму {$totalPaid} ₽",
                'paid_count' => $paidCount,
                'total_paid' => $totalPaid,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Получить статистику доходов
     */
    public function getRevenueStats(Request $request)
    {
        $request->validate([
            'period' => 'required|in:day,week,month,year',
        ]);
        
        $endDate = Carbon::now();
        
        switch ($request->period) {
            case 'day':
                $startDate = Carbon::today();
                $format = 'H:i';
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $format = 'D';
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $format = 'd.m';
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $format = 'M';
                break;
            default:
                $startDate = Carbon::today();
                $format = 'H:i';
        }
        
        $revenues = ClientContract::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as period"),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        return response()->json($revenues);
    }
    
    /**
     * Страница управления поставщиками
     */
    public function suppliers()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();
        
        $suppliers = Supplier::orderBy('supplier_name')->get();
        
        return Inertia::render('Accountant/Suppliers', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'suppliers' => $suppliers,
            'unpaidCount' => $stats['unpaidCount'],
            'criticalCount' => $stats['criticalCount'],
            'todayRevenue' => $stats['todayRevenue'],
            'pendingPayments' => $stats['pendingPayments'],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Создать нового поставщика
     */
    public function createSupplier(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $supplier = Supplier::create($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Поставщик добавлен',
            'supplier' => $supplier,
        ]);
    }
    
    /**
     * Обновить данные поставщика
     */
    public function updateSupplier(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $supplier->update($request->all());
        
        return response()->json([
            'success' => true,
            'message' => 'Данные поставщика обновлены',
            'supplier' => $supplier,
        ]);
    }
    
    /**
     * Удалить поставщика
     */
    public function deleteSupplier($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        // Проверяем, есть ли у поставщика активные контракты
        $hasActiveContracts = $supplier->contracts()
            ->where('status', 1)
            ->exists();
        
        if ($hasActiveContracts) {
            return response()->json([
                'success' => false,
                'error' => 'Нельзя удалить поставщика с активными контрактами',
            ], 422);
        }
        
        $supplier->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Поставщик удален',
        ]);
    }
    

    /**
     * Страница профиля бухгалтера
     */
    public function profile()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();
        
        // Получаем статистику для профиля
        $appointmentsCount = Appointment::where('employee_id', $user->employee_id)->count();
        $completedCount = Appointment::where('employee_id', $user->employee_id)
            ->where('status', 2)
            ->count();
        
        return Inertia::render('Accountant/Profile', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
                'hourly_rate' => $user->hourly_rate,
            ],
            'stats' => [
                'total_appointments' => $appointmentsCount,
                'completed_appointments' => $completedCount,
                'unpaid_count' => $stats['unpaidCount'],
                'critical_count' => $stats['criticalCount'],
                'today_revenue' => $stats['todayRevenue'],
                'pending_payments' => $stats['pendingPayments'],
            ],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }

    /**
     * Обновить профиль бухгалтера
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('employee')->user();
        
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $user->employee_id . ',employee_id',
            'employee_phone' => 'nullable|string|max:20',
        ]);
        
        $user->employee_name = $request->employee_name;
        $user->email = $request->email;
        $user->employee_phone = $request->employee_phone;
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Профиль успешно обновлен',
        ]);
    }

    /**
     * Загрузить фото профиля
     */
    public function uploadPhoto(Request $request)
    {
        $user = Auth::guard('employee')->user();
        
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }
        
        $path = $request->file('photo')->store('employees', 'public');
        
        $user->photo = $path;
        $user->save();
        
        return response()->json([
            'success' => true,
            'photo_url' => Storage::url($path),
            'message' => 'Фото успешно загружено',
        ]);
    }

    /**
     * Удалить фото профиля
     */
    public function deletePhoto()
    {
        $user = Auth::guard('employee')->user();
        
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
            $user->photo = null;
            $user->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Фото удалено',
        ]);
    }
}