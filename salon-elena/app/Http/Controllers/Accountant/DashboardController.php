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
            
            $todayRevenue = ClientContract::whereDate('created_at', $today)->sum('total_amount');
            
            $unpaidServices = Appointment::where('status', 2)
                ->whereDoesntHave('clientContract')
                ->get();
            $pendingPayments = 0;
            foreach ($unpaidServices as $service) {
                $pendingPayments += $service->calculateTotalPrice();
            }
            
            $criticalCount = Material::where('current_balance', '<=', DB::raw('min_stock'))->count();
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
        
        // Получаем все материалы (без фильтрации по поставщику)
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
                    'supplier_price' => null,
                    'status' => $status,
                ];
            });
        
        $suppliers = Supplier::all()->map(function($supplier) {
            return [
                'supplier_id' => $supplier->supplier_id,
                'supplier_name' => $supplier->supplier_name,
            ];
        });
        
        // Получаем материалы для каждого поставщика (для быстрого доступа)
        $supplierMaterials = [];
        foreach ($suppliers as $supplier) {
            $materialsForSupplier = DB::table('supplier_materials')
                ->join('materials', 'supplier_materials.material_id', '=', 'materials.material_id')
                ->where('supplier_materials.supplier_id', $supplier['supplier_id'])
                ->select('materials.material_id', 'materials.name', 'supplier_materials.price')
                ->get();
            
            $supplierMaterials[$supplier['supplier_id']] = $materialsForSupplier;
        }
        
        // Активные заказы (статус 1 - подтвержден, в пути)
        $activeOrders = SupplierContract::where('status', SupplierContract::STATUS_CONFIRMED)
            ->with(['supplier', 'materialReceipts.material'])
            ->get()
            ->map(function($contract) {
                return [
                    'id' => $contract->contract_id,
                    'number' => $contract->number,
                    'date' => $contract->date,
                    'supplier_name' => $contract->supplier?->supplier_name,
                    'status' => $contract->status,
                    'status_text' => $contract->status_text,
                    'total_amount' => $contract->materialReceipts->sum(function($receipt) {
                        return $receipt->quantity * $receipt->price;
                    }),
                    'items' => $contract->materialReceipts->map(function($receipt) {
                        return [
                            'material_name' => $receipt->material->name,
                            'quantity' => $receipt->quantity,
                            'price' => $receipt->price,
                        ];
                    }),
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
            'supplierMaterials' => $supplierMaterials,
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
            // Генерируем номер заказа
            $contractNumber = 'PO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Создаем договор с поставщиком (статус 0 - не подтвержден)
            $contract = SupplierContract::create([
                'number' => $contractNumber,
                'date' => now(),
                'status' => SupplierContract::STATUS_PENDING,
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
                    'status' => MaterialReceipt::STATUS_PENDING,
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
        $receipt = MaterialReceipt::with(['material', 'supplierContract'])
            ->findOrFail($receiptId);
        
        if ($receipt->status == MaterialReceipt::STATUS_RECEIVED) {
            return response()->json(['error' => 'Товар уже принят'], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Увеличиваем остаток материала
            $material = $receipt->material;
            $material->current_balance += $receipt->quantity;
            $material->save();
            
            // Обновляем статус поступления
            $receipt->status = MaterialReceipt::STATUS_RECEIVED;
            $receipt->save();
            
            // Проверяем, все ли материалы в этом заказе получены
            $order = $receipt->supplierContract;
            $allReceived = $order->materialReceipts->every(function($r) {
                return $r->status == MaterialReceipt::STATUS_RECEIVED;
            });
            
            // Если все материалы получены, обновляем статус заказа
            if ($allReceived && $order->status != SupplierContract::STATUS_RECEIVED) {
                $order->status = SupplierContract::STATUS_RECEIVED;
                $order->received_at = now();
                $order->save();
            }
            
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
     * Получить материалы для склада с фильтрацией по поставщику
     */
    public function getWarehouseMaterials(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        
        if ($supplierId) {
            // Получаем материалы, закрепленные за поставщиком
            $supplier = Supplier::findOrFail($supplierId);
            $materials = $supplier->materials()
                ->orderBy('materials.name')
                ->get()
                ->map(function($material) use ($supplierId) {
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
                        'price_per_unit' => $material->price_per_unit, // Цена для клиентов
                        'supplier_price' => $material->pivot->price,   // Цена от поставщика
                        'status' => $status,
                    ];
                });
        } else {
            // Получаем все материалы (без привязки к поставщику) - здесь нет кнопки "В заказ"
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
                        'supplier_price' => null,
                        'status' => $status,
                    ];
                });
        }
        
        return response()->json($materials);
    }

        /**
     * Обновить статус заказа
     */
    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:0,1,2,3',
        ]);
        
        $order = SupplierContract::with(['materialReceipts.material'])
            ->findOrFail($orderId);
        
        // Нельзя менять статус полученного заказа
        if ($order->status == SupplierContract::STATUS_RECEIVED) {
            return response()->json(['error' => 'Нельзя изменить статус полученного заказа'], 422);
        }
        
        $oldStatus = $order->status;
        $order->status = $request->status;
        
        // Устанавливаем даты
        if ($request->status == SupplierContract::STATUS_CONFIRMED && $oldStatus == SupplierContract::STATUS_PENDING) {
            $order->confirmed_at = now();
        }
        
        if ($request->status == SupplierContract::STATUS_RECEIVED) {
            $order->received_at = now();
            // Автоматически принимаем все материалы на склад
            foreach ($order->materialReceipts as $receipt) {
                if ($receipt->status == MaterialReceipt::STATUS_PENDING) {
                    $this->processReceiveOrder($receipt->receipt_id);
                }
            }
        }
        
        $order->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Статус заказа обновлен',
            'status' => $order->status,
            'status_text' => $order->status_text,
            'confirmed_at' => $order->confirmed_at,
            'received_at' => $order->received_at,
        ]);
    }
        /**
     * Внутренний метод для приема заказа
     */
    private function processReceiveOrder($receiptId)
    {
        $receipt = MaterialReceipt::with(['material', 'supplierContract'])
            ->findOrFail($receiptId);
        
        if ($receipt->status == MaterialReceipt::STATUS_RECEIVED) {
            return;
        }
        
        // Увеличиваем остаток материала
        $material = $receipt->material;
        $material->current_balance += $receipt->quantity;
        $material->save();
        
        // Обновляем статус поступления
        $receipt->status = MaterialReceipt::STATUS_RECEIVED;
        $receipt->save();
    }

    public function getOrders(Request $request)
    {
        $status = $request->get('status');
        
        $query = SupplierContract::with(['supplier', 'materialReceipts.material']);
        
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get()
            ->map(function($order) {
                return [
                    'id' => $order->contract_id,
                    'number' => $order->number,
                    'date' => $order->date,
                    'supplier_name' => $order->supplier?->supplier_name,
                    'status' => $order->status,
                    'status_text' => $order->status_text,
                    'total_amount' => $order->materialReceipts->sum(function($receipt) {
                        return $receipt->quantity * $receipt->price;
                    }),
                    'created_at' => $order->created_at,
                    'confirmed_at' => $order->confirmed_at,
                    'received_at' => $order->received_at,
                    'items' => $order->materialReceipts->map(function($receipt) {
                        return [
                            'material_name' => $receipt->material->name,
                            'quantity' => $receipt->quantity,
                            'price' => $receipt->price,
                            'unit' => $receipt->material->unit,
                            'total' => $receipt->quantity * $receipt->price,
                        ];
                    }),
                ];
            });
        
        return response()->json($orders);
    }
        /**
     * Страница управления заказами
     */
    public function orders()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();
        
        return Inertia::render('Accountant/Orders', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'unpaidCount' => $stats['unpaidCount'],
            'criticalCount' => $stats['criticalCount'],
            'todayRevenue' => $stats['todayRevenue'],
            'pendingPayments' => $stats['pendingPayments'],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
        /**
     * Получить данные заказа для документа
     */
    public function getOrderDocument($orderId)
    {
        $order = SupplierContract::with(['supplier', 'materialReceipts.material'])
            ->findOrFail($orderId);
        
        // Добавляем город (можно вынести в настройки)
        $city = 'Москва';
        
        return response()->json([
            'id' => $order->contract_id,
            'number' => $order->number,
            'created_at' => $order->created_at,
            'confirmed_at' => $order->confirmed_at,
            'received_at' => $order->received_at,
            'total_amount' => $order->materialReceipts->sum(function($receipt) {
                return $receipt->quantity * $receipt->price;
            }),
            'supplier_name' => $order->supplier?->supplier_name,
            'supplier_inn' => $order->supplier?->inn,
            'supplier_address' => $order->supplier?->address,
            'supplier_phone' => $order->supplier?->phone,
            'supplier_email' => $order->supplier?->email,
            'city' => $city,
            'items' => $order->materialReceipts->map(function($receipt) {
                return [
                    'material_name' => $receipt->material->name,
                    'quantity' => $receipt->quantity,
                    'price' => $receipt->price,
                    'unit' => $receipt->material->unit,
                    'total' => $receipt->quantity * $receipt->price,
                ];
            }),
        ]);
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
        
        return Inertia::render('Accountant/Salary', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'employees' => [], // Будет загружено через API
            'totalSalary' => 0,
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
     * Получить данные для расчета зарплаты (GET запрос)
     */
    public function calculateSalaryData(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
        ]);
        
        $month = (int) $request->month;
        $year = (int) $request->year;
        
        // Получаем всех сотрудников с ролью doctor, admin, director
        $employees = Employee::whereIn('role', ['doctor', 'admin', 'director'])
            ->orderBy('role')
            ->orderBy('employee_name')
            ->get()
            ->map(function($employee) use ($month, $year) {
                // Получаем существующую запись о зарплате, если есть
                $existingSalary = $employee->salaries()
                    ->where('month', $month)
                    ->where('year', $year)
                    ->first();
                
                if ($existingSalary) {
                    $calculationData = $existingSalary->calculation_details;
                    $hoursWorked = $existingSalary->hours_worked;
                    $totalAmount = $existingSalary->total_amount;
                    $isPaid = $existingSalary->is_paid;
                    $salaryId = $existingSalary->salary_id;
                } else {
                    $hoursWorked = 0;
                    $totalAmount = 0;
                    $isPaid = false;
                    $salaryId = null;
                }
                
                return [
                    'id' => $employee->employee_id,
                    'name' => $employee->employee_name,
                    'role' => $employee->role,
                    'hourly_rate' => $employee->hourly_rate ?? 0,
                    'hours_worked' => $hoursWorked,
                    'total_amount' => $totalAmount,
                    'is_paid' => $isPaid,
                    'salary_id' => $salaryId,
                    'calculation_details' => $existingSalary ? $existingSalary->calculation_details : null,
                ];
            });
        
        $totalSalary = $employees->sum('total_amount');
        
        return response()->json([
            'employees' => $employees,
            'totalSalary' => $totalSalary,
        ]);
    }

    /**
     * Сохранить расчет зарплаты (POST запрос)
     */
    public function saveSalaryCalculation(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'payment_type' => 'required|in:hourly,daily',
            'hours_or_days' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
        ]);
        
        $employee = Employee::findOrFail($request->employee_id);
        $bonus = $request->bonus ?? 0;
        
        DB::beginTransaction();
        
        try {
            $salary = $employee->saveSalaryCalculation(
                $request->month,
                $request->year,
                $request->payment_type,
                $request->hours_or_days,
                $bonus
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Расчет сохранен',
                'salary_id' => $salary->salary_id,
                'calculation' => $salary->calculation_details,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Рассчитать зарплату (предпросмотр)
     */
    public function calculateSalaryPreview(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,employee_id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'payment_type' => 'required|in:hourly,daily',
            'hours_or_days' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
        ]);
        
        $employee = Employee::findOrFail($request->employee_id);
        $bonus = $request->bonus ?? 0;
        
        $calculation = $employee->calculateSalary(
            $request->month,
            $request->year,
            $request->payment_type,
            $request->hours_or_days,
            $bonus
        );
        
        return response()->json($calculation);
    }

    /**
     * Выплатить зарплату сотруднику
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
            $salary = $employee->salaries()
                ->where('month', $request->month)
                ->where('year', $request->year)
                ->first();
            
            if (!$salary) {
                return response()->json(['error' => 'Расчет не найден'], 404);
            }
            
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
                $salary = $employee->salaries()
                    ->where('month', $request->month)
                    ->where('year', $request->year)
                    ->first();
                
                if ($salary && !$salary->is_paid) {
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
     * Получить историю начислений зарплаты
     */
    public function getSalaryHistory(Request $request)
    {
        $query = Salary::with(['employee'])
            ->orderBy('created_at', 'desc');

        if ($request->employee_name) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('employee_name', 'like', '%' . $request->employee_name . '%');
            });
        }

        if ($request->month) {
            $query->where('month', $request->month);
        }
        if ($request->year) {
            $query->where('year', $request->year);
        }

        $salaries = $query->get()->map(function($salary) {
            $calculationData = $salary->calculation_details;
            
            return [
                'id' => $salary->salary_id,
                'employee_name' => $salary->employee->employee_name,
                'employee_role' => $salary->employee->role,
                'month' => $salary->month,
                'year' => $salary->year,
                'hours_worked' => $salary->hours_worked,
                'hourly_rate' => $salary->hourly_rate,
                'total_amount' => $salary->total_amount,
                'is_paid' => $salary->is_paid,
                'payment_date' => $salary->payment_date ? Carbon::parse($salary->payment_date)->format('d.m.Y') : null,
                'calculation_data' => $calculationData,
                'created_at' => $salary->created_at,
                'formatted_date' => Carbon::parse($salary->created_at)->format('d.m.Y H:i'),
            ];
        });

        return response()->json([
            'salaries' => $salaries,
            'total_sum' => $salaries->sum('total_amount')
        ]);
    }
    
    /**
     * Получить детали чека зарплаты
     */
    public function getSalaryReceiptDetails($salaryId)
    {
        $salary = Salary::with(['employee'])
            ->findOrFail($salaryId);
        
        $calculationData = $salary->calculation_data;
        
        if (is_string($calculationData)) {
            $calculationData = json_decode($calculationData, true);
            if (is_string($calculationData)) {
                $calculationData = json_decode($calculationData, true);
            }
        }
        
        if (!$calculationData || empty($calculationData)) {
            $calculationData = [
                'base_salary' => $salary->hourly_rate * $salary->hours_worked,
                'bonus' => 0,
                'total_accrued' => $salary->hourly_rate * $salary->hours_worked,
                'ndfl' => round(($salary->hourly_rate * $salary->hours_worked) * 0.13, 2),
                'net_salary' => $salary->total_amount,
                'payment_type' => 'hourly',
                'hours_or_days' => $salary->hours_worked,
            ];
        }
        
        return response()->json([
            'salary_id' => $salary->salary_id,
            'employee_name' => $salary->employee->employee_name,
            'employee_role' => $salary->employee->role,
            'month' => $salary->month,
            'year' => $salary->year,
            'month_name' => $this->getMonthName($salary->month),
            'hourly_rate' => $salary->hourly_rate,
            'hours_worked' => $salary->hours_worked,
            'total_amount' => $salary->total_amount,
            'is_paid' => (bool)$salary->is_paid,
            'payment_date' => $salary->payment_date ? Carbon::parse($salary->payment_date)->format('d.m.Y') : null,
            'created_at' => Carbon::parse($salary->created_at)->format('d.m.Y H:i'),
            'calculation_details' => [
                'base_salary' => $calculationData['base_salary'] ?? 0,
                'bonus' => $calculationData['bonus'] ?? 0,
                'total_accrued' => $calculationData['total_accrued'] ?? ($salary->hourly_rate * $salary->hours_worked),
                'ndfl' => $calculationData['ndfl'] ?? 0,
                'net_salary' => $calculationData['net_salary'] ?? $salary->total_amount,
                'payment_type' => $calculationData['payment_type'] ?? 'hourly',
                'hours_or_days' => $calculationData['hours_or_days'] ?? $salary->hours_worked,
            ]
        ]);
    }
    
    /**
     * Получить форму расчета зарплаты для сотрудника
     */
    public function getSalaryForm($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $currentMonth = date('n');
        $currentYear = date('Y');
        
        $existingSalary = $employee->salaries()
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first();
        
        return response()->json([
            'employee' => [
                'id' => $employee->employee_id,
                'name' => $employee->employee_name,
                'role' => $employee->role,
                'hourly_rate' => $employee->hourly_rate,
            ],
            'existing_calculation' => $existingSalary ? $existingSalary->calculation_details : null,
            'current_month' => $currentMonth,
            'current_year' => $currentYear,
        ]);
    }

    /**
     * Обновить почасовую ставку сотрудника
     */
    public function updateHourlyRate(Request $request, $employeeId)
    {
        $request->validate([
            'hourly_rate' => 'required|numeric|min:0|max:10000',
        ]);
        
        $employee = Employee::findOrFail($employeeId);
        $employee->hourly_rate = $request->hourly_rate;
        $employee->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Ставка обновлена',
            'hourly_rate' => $employee->hourly_rate,
        ]);
    }
    
    /**
     * Вспомогательный метод для получения названия месяца
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
            5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
            9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];
        return $months[$month] ?? '';
    }

    
        /**
     * Страница управления поставщиками
     */
    public function suppliers()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();
        
        $suppliers = Supplier::with('materials')->orderBy('supplier_name')->get();
        
        $suppliers = $suppliers->map(function($supplier) {
            return [
                'supplier_id' => $supplier->supplier_id,
                'supplier_name' => $supplier->supplier_name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'notes' => $supplier->notes,
                'inn' => $supplier->inn,
                'director_fio' => $supplier->director_fio,
                'accountant_fio' => $supplier->accountant_fio,
                'bank_name' => $supplier->bank_name,
                'bic' => $supplier->bic,
                'payment_account' => $supplier->payment_account,
                'materials' => $supplier->materials->map(function($material) {
                    return [
                        'material_id' => $material->material_id,
                        'name' => $material->name,
                        'unit' => $material->unit,
                        'price' => $material->pivot->price,
                    ];
                }),
                'created_at' => $supplier->created_at,
            ];
        });
        
        // Получаем все материалы для выбора
        $allMaterials = Material::orderBy('name')->get();
        
        return Inertia::render('Accountant/Suppliers', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'suppliers' => $suppliers,
            'allMaterials' => $allMaterials,
            'unpaidCount' => $stats['unpaidCount'],
            'criticalCount' => $stats['criticalCount'],
            'todayRevenue' => $stats['todayRevenue'],
            'pendingPayments' => $stats['pendingPayments'],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
        /**
     * Создать новый материал
     */
    public function createMaterial(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'unit' => 'required|string|max:100',
            'min_stock' => 'required|integer|min:0',
            'current_balance' => 'required|integer|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
        ]);
        
        try {
            $material = Material::create([
                'name' => $request->name,
                'unit' => $request->unit,
                'min_stock' => $request->min_stock,
                'current_balance' => $request->current_balance,
                'price_per_unit' => $request->price_per_unit,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Материал успешно добавлен',
                'material' => $material
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при добавлении материала: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновить материал
     */
    public function updateMaterial(Request $request, $id)
    {
        $material = Material::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:100',
            'unit' => 'required|string|max:100',
            'min_stock' => 'required|integer|min:0',
            'current_balance' => 'required|integer|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
        ]);
        
        try {
            $material->update([
                'name' => $request->name,
                'unit' => $request->unit,
                'min_stock' => $request->min_stock,
                'current_balance' => $request->current_balance,
                'price_per_unit' => $request->price_per_unit,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Материал успешно обновлен',
                'material' => $material
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при обновлении материала: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удалить материал
     */
    public function deleteMaterial($id)
    {
        $material = Material::findOrFail($id);
        
        // Проверяем, используется ли материал в расходах
        $hasConsumptions = $material->consumptions()->exists();
        // Проверяем, используется ли материал в записях
        $hasAppointments = $material->appointments()->exists();
        // Проверяем, есть ли у поставщиков этот материал
        $hasSuppliers = $material->suppliers()->exists();
        
        if ($hasConsumptions || $hasAppointments || $hasSuppliers) {
            return response()->json([
                'success' => false,
                'error' => 'Материал нельзя удалить, так как он используется в записях, расходах или у поставщиков'
            ], 422);
        }
        
        try {
            $material->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Материал успешно удален'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при удалении материала: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Создать нового поставщика
     */
    public function createSupplier(Request $request)
    {
        $user = Auth::guard('employee')->user();

        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'inn' => 'nullable|string|max:20',
            'director_fio' => 'nullable|string|max:255',
            'accountant_fio' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bic' => 'nullable|string|max:20',
            'payment_account' => 'nullable|string|max:50',
        ]);
        
        $supplier = Supplier::create([
            'supplier_name' => $request->supplier_name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
            'inn' => $request->inn,
            'director_fio' => $request->director_fio,
            'accountant_fio' => $user->employee_name,
            'bank_name' => $request->bank_name,
            'bic' => $request->bic,
            'payment_account' => $request->payment_account,
        ]);
        
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
        $user = Auth::guard('employee')->user();
        $supplier = Supplier::findOrFail($id);
        
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'inn' => 'nullable|string|max:20',
            'director_fio' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bic' => 'nullable|string|max:20',
            'payment_account' => 'nullable|string|max:50',
        ]);
        
        $supplier->update([
            'supplier_name' => $request->supplier_name,
            'contact_person' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'notes' => $request->notes,
            'inn' => $request->inn,
            'director_fio' => $request->director_fio,
            'accountant_fio' => $user->employee_name, // При обновлении тоже подставляем имя текущего бухгалтера
            'bank_name' => $request->bank_name,
            'bic' => $request->bic,
            'payment_account' => $request->payment_account,
        ]);
        
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
     * Получить материалы поставщика
     */
    public function getSupplierMaterials($supplierId)
    {
        try {
            $materials = DB::table('supplier_materials')
                ->join('materials', 'supplier_materials.material_id', '=', 'materials.material_id')
                ->where('supplier_materials.supplier_id', $supplierId)
                ->select('materials.material_id', 'materials.name', 'materials.unit', 'supplier_materials.price', 'supplier_materials.is_active')
                ->get();
            
            $supplier = Supplier::findOrFail($supplierId);
            
            return response()->json([
                'supplier' => $supplier,
                'materials' => $materials,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting supplier materials: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }

        /**
     * Добавить материал поставщику
     */
    public function addSupplierMaterial(Request $request, $supplierId)
    {
        try {
            $request->validate([
                'material_id' => 'required|exists:materials,material_id',
                'price' => 'nullable|numeric|min:0',
            ]);
            
            $supplier = Supplier::findOrFail($supplierId);
            
            // Проверяем, не добавлен ли уже этот материал (указываем таблицу явно)
            $exists = DB::table('supplier_materials')
                ->where('supplier_id', $supplierId)
                ->where('material_id', $request->material_id)
                ->exists();
            
            if ($exists) {
                return response()->json(['error' => 'Этот материал уже добавлен поставщику'], 422);
            }
            
            // Добавляем материал
            DB::table('supplier_materials')->insert([
                'supplier_id' => $supplierId,
                'material_id' => $request->material_id,
                'price' => $request->price,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Получаем обновленный список материалов
            $materials = DB::table('supplier_materials')
                ->join('materials', 'supplier_materials.material_id', '=', 'materials.material_id')
                ->where('supplier_materials.supplier_id', $supplierId)
                ->select('materials.material_id', 'materials.name', 'materials.unit', 'supplier_materials.price')
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Материал добавлен поставщику',
                'materials' => $materials,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error adding supplier material: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Обновить материал поставщика
     */
    public function updateSupplierMaterial(Request $request, $supplierId, $materialId)
    {
        $request->validate([
            'price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        
        $supplier = Supplier::findOrFail($supplierId);
        
        $supplier->materials()->updateExistingPivot($materialId, [
            'price' => $request->price,
            'is_active' => $request->is_active ?? true,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Данные материала обновлены',
        ]);
    }

    /**
     * Удалить материал поставщика
     */
    public function removeSupplierMaterial($supplierId, $materialId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        $supplier->materials()->detach($materialId);
        
        return response()->json([
            'success' => true,
            'message' => 'Материал удален из списка поставщика',
        ]);
    }
        /**
     * Обновить цену материала у поставщика
    */
    public function updateSupplierMaterialPrice(Request $request, $supplierId, $materialId)
    {
        try {
            $request->validate([
                'price' => 'required|numeric|min:0',
            ]);
            
            $supplier = Supplier::findOrFail($supplierId);
            
            // Используем Query Builder напрямую, чтобы избежать конфликта имен колонок
            $updated = DB::table('supplier_materials')
                ->where('supplier_id', $supplierId)
                ->where('material_id', $materialId)
                ->update([
                    'price' => $request->price,
                    'updated_at' => now()
                ]);
            
            if (!$updated) {
                return response()->json(['error' => 'Материал не найден у этого поставщика'], 422);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Цена материала обновлена',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error updating supplier material price: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при обновлении цены: ' . $e->getMessage()], 500);
        }
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

   /**
     * Страница доходов (вместо payments)
     */
    public function incomes()
    {
        $user = Auth::guard('employee')->user();
        $stats = $this->getSidebarStats();

        return Inertia::render('Accountant/Incomes', [
            'accountant' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'unpaidCount' => $stats['unpaidCount'],
            'criticalCount' => $stats['criticalCount'],
            'todayRevenue' => $stats['todayRevenue'],
            'pendingPayments' => $stats['pendingPayments'],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }

    /**
     * Получить список доходов с фильтрацией
     */
    public function getIncomesList(Request $request)
    {
        $query = ClientContract::with([
            'client',
            'employee',
            'appointment.providedServices.service',
            'appointment.materials'
        ])->where('status', 1); // Только оплаченные контракты

        // Фильтр по дате
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Фильтр по клиенту
        if ($request->client_search) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('client_name', 'like', '%' . $request->client_search . '%');
            });
        }

        // Фильтр по услуге
        if ($request->service_search) {
            $query->whereHas('appointment.providedServices.service', function($q) use ($request) {
                $q->where('service_name', 'like', '%' . $request->service_search . '%');
            });
        }

        $contracts = $query->orderBy('created_at', 'desc')->get();

        $incomes = $contracts->map(function($contract) {
            $appointment = $contract->appointment;
            
            // Получаем список услуг
            $servicesList = $appointment->providedServices->map(function($ps) {
                return $ps->service ? $ps->service->service_name : null;
            })->filter()->join(', ');
            
            return [
                'id' => $contract->contract_id,
                'contract_number' => $contract->contract_number,
                'date' => $contract->created_at,
                'formatted_date' => Carbon::parse($contract->created_at)->format('d.m.Y H:i'),
                'client_name' => $contract->client->client_name,
                'services_list' => $servicesList ?: 'Услуга не указана',
                'total_price' => $appointment->calculateTotalPrice(),
                'employee_name' => $contract->employee->employee_name,
            ];
        });

        return response()->json([
            'incomes' => $incomes,
            'total_sum' => $incomes->sum('total_price')
        ]);
    }

    /**
     * Получить детали чека для просмотра
     */
    public function getReceiptDetails($contractId)
    {
        $contract = ClientContract::with([
            'client',
            'employee',
            'appointment' => function($query) {
                $query->with(['providedServices.service', 'materials', 'employee']);
            }
        ])->findOrFail($contractId);

        $appointment = $contract->appointment;

        // Данные об услугах
        $services = $appointment->providedServices->map(function($ps) {
            return [
                'name' => $ps->service->service_name,
                'quantity' => $ps->quantity,
                'price' => $ps->service->default_price,
                'total' => $ps->service->default_price * $ps->quantity,
            ];
        });

        // Данные о материалах
        $materials = $appointment->materials->map(function($material) {
            return [
                'name' => $material->name,
                'quantity' => $material->pivot->quantity_used,
                'price' => $material->pivot->cost_price,
                'unit' => $material->unit,
                'total' => $material->pivot->cost_price * $material->pivot->quantity_used,
            ];
        });

        $totalServices = $services->sum('total');
        $totalMaterials = $materials->sum('total');
        $totalAmount = $totalServices + $totalMaterials;

        return response()->json([
            'contract_number' => $contract->contract_number,
            'date' => Carbon::parse($contract->created_at)->format('d.m.Y H:i'),
            'client' => [
                'name' => $contract->client->client_name,
                'phone' => $contract->client->phone,
                'email' => $contract->client->email,
            ],
            'doctor' => [
                'name' => $appointment->employee->employee_name,
            ],
            'accountant' => [
                'name' => $contract->employee->employee_name,
            ],
            'services' => $services,
            'materials' => $materials,
            'total_services' => $totalServices,
            'total_materials' => $totalMaterials,
            'total_amount' => $totalAmount,
        ]);
    }
    /**
     * Вспомогательный метод для получения названия роли
     */
    private function getRoleName($role)
    {
        $roles = [
            'doctor' => 'Врач',
            'admin' => 'Администратор',
            'director' => 'Директор',
            'accountant' => 'Бухгалтер'
        ];
        return $roles[$role] ?? $role;
    }
}