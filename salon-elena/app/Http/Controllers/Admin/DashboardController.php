<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Service;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\SupplierContract;
use App\Models\MaterialReceipt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Главная страница администратора
     */
    public function index()
    {
        $user = Auth::guard('employee')->user();
        
        // Статистика на сегодня
        $todayAppointments = Appointment::whereDate('date', Carbon::today())->count();
        
        $todayClients = Appointment::whereDate('date', Carbon::today())
            ->distinct('client_id')
            ->count('client_id');
        
        $activeAppointments = Appointment::whereIn('status', [0, 1])
            ->whereDate('date', '>=', Carbon::today())
            ->count();
        
        $criticalCount = Material::where('current_balance', '<=', DB::raw('min_stock'))->count();
        
        // Ближайшие записи
        $upcomingAppointments = Appointment::with(['client', 'employee', 'providedServices.service'])
            ->where('date', '>=', Carbon::now())
            ->whereIn('status', [0, 1])
            ->orderBy('date', 'asc')
            ->take(10)
            ->get()
            ->map(function($appointment) {
                return [
                    'id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'time' => Carbon::parse($appointment->date)->format('H:i'),
                    'client_name' => $appointment->client->client_name,
                    'doctor_name' => $appointment->employee->employee_name,
                    'service_name' => $appointment->services_list,
                ];
            });
        
        return Inertia::render('Admin/Dashboard', [
            'admin' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'stats' => [
                'today_appointments' => $todayAppointments,
                'today_clients' => $todayClients,
                'active_appointments' => $activeAppointments,
                'critical_count' => $criticalCount,
            ],
            'upcomingAppointments' => $upcomingAppointments,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница записи клиентов
     */
    public function appointments()
    {
        $user = Auth::guard('employee')->user();
        
        $doctors = Employee::where('role', 'doctor')
        ->with('services')
        ->orderBy('employee_name')
        ->get()
        ->map(function($doctor) {
            return [
                'employee_id' => $doctor->employee_id,
                'employee_name' => $doctor->employee_name,
                'services' => $doctor->services->map(function($service) {
                    return [
                        'service_id' => $service->service_id,
                        'service_name' => $service->service_name,
                    ];
                }),
            ];
        });
        
        $services = Service::where('is_active', 1)
            ->orderBy('service_category')
            ->orderBy('service_name')
            ->get();
        
        $existingAppointments = Appointment::with(['client', 'employee', 'providedServices.service'])
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date', 'asc')
            ->get()
            ->map(function($appointment) {
                return [
                    'id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'time' => Carbon::parse($appointment->date)->format('H:i'),
                    'client_name' => $appointment->client->client_name,
                    'doctor_name' => $appointment->employee->employee_name,
                    'service_name' => $appointment->services_list,
                    'status' => $appointment->status,
                    'status_text' => $appointment->status_text,
                ];
            });
        
        return Inertia::render('Admin/Appointments', [
            'admin' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'doctors' => $doctors,
            'services' => $services,
            'appointments' => $existingAppointments,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    /**
     * Страница поставок
     */
    public function orders()
    {
        $user = Auth::guard('employee')->user();
        
        return Inertia::render('Admin/Orders', [
            'admin' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'criticalCount' => Material::where('current_balance', '<=', DB::raw('min_stock'))->count(),
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Поиск клиентов
     */
    public function searchClients(Request $request)
    {
        $query = $request->get('query');
        
        $clients = Client::where('client_name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(10)
            ->get(['client_id', 'client_name', 'phone', 'email']);
        
        return response()->json(['clients' => $clients]);
    }
    
    /**
     * Создать нового клиента (быстрая регистрация)
     */
    public function createClient(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|unique:clients,email',
        ]);
        
        // Очищаем телефон от лишних символов для правильного формата
        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
        
        // Приводим к формату +7XXXXXXXXXX
        if (strlen($cleanPhone) === 11 && substr($cleanPhone, 0, 1) === '8') {
            $cleanPhone = '7' . substr($cleanPhone, 1);
        }
        
        if (strlen($cleanPhone) === 11 && substr($cleanPhone, 0, 1) === '7') {
            $formattedPhone = '+' . $cleanPhone;
        } else if (strlen($cleanPhone) === 10) {
            $formattedPhone = '+7' . $cleanPhone;
        } else {
            $formattedPhone = '+' . $cleanPhone;
        }
        
        // Генерируем уникальный логин
        $login = 'client_' . time() . '_' . rand(100, 999);
        $tempPassword = 'password123';
        
        try {
            $client = Client::create([
                'client_name' => $request->name,
                'phone' => $formattedPhone,
                'email' => $request->email,
                'login' => $login,
                'passwd' => bcrypt($tempPassword),
                'birth_date' => now()->subYears(25),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Клиент успешно создан',
                'client' => [
                    'client_id' => $client->client_id,
                    'client_name' => $client->client_name,
                    'phone' => $client->phone,
                    'email' => $client->email,
                ],
                'temp_password' => $tempPassword,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error creating client: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании клиента: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Создать запись на прием
     */
    public function createAppointment(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,client_id',
            'doctor_id' => 'required|exists:employees,employee_id',
            'service_id' => 'required|exists:services,service_id',
            'date' => 'required|date|after:today',
            'time' => 'required|string',
        ]);
        
        $dateTime = Carbon::parse($request->date . ' ' . $request->time);
        
        $exists = Appointment::where('employee_id', $request->doctor_id)
            ->whereDate('date', $dateTime->toDateString())
            ->whereTime('date', $dateTime->toTimeString())
            ->whereIn('status', [0, 1])
            ->exists();
        
        if ($exists) {
            return response()->json(['error' => 'Это время уже занято'], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $appointment = Appointment::create([
                'date' => $dateTime,
                'status' => 0,
                'employee_id' => $request->doctor_id,
                'client_id' => $request->client_id,
            ]);
            
            DB::table('provided_services')->insert([
                'quantity' => 1,
                'service_date' => $dateTime->toDateString(),
                'notes' => $request->notes ?? '',
                'appointment_id' => $appointment->appointment_id,
                'service_id' => $request->service_id,
                'employee_id' => $request->doctor_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Запись успешно создана',
                'appointment' => $appointment->load(['client', 'employee', 'providedServices.service'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Отменить запись
     */
    public function cancelAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = 3;
        $appointment->save();
        
        return response()->json(['success' => true]);
    }
    /**
     * Получить доступные слоты для записи
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'doctor_id' => 'required|exists:employees,employee_id',
        ]);
        
        $doctor = Employee::findOrFail($request->doctor_id);
        $date = Carbon::parse($request->date);
        $dayOfWeek = $date->dayOfWeek;
        
        $schedule = $doctor->getScheduleForDay($dayOfWeek);
        
        if (!$schedule || !$schedule->start_time || !$schedule->end_time) {
            return response()->json(['available_slots' => []]);
        }
        
        $workHours = $doctor->getWorkingHoursForDate($date);
        
        $busySlots = Appointment::where('employee_id', $doctor->employee_id)
            ->whereDate('date', $date->toDateString())
            ->whereIn('status', [0, 1])
            ->get()
            ->map(function($appointment) use ($date) {
                return Carbon::parse($appointment->date)->format('H:i');
            })
            ->toArray();
        
        $availableSlots = [];
        $now = Carbon::now();
        
        foreach ($workHours as $time) {
            if (!in_array($time, $busySlots)) {
                if ($date->isToday()) {
                    $slotTime = Carbon::parse($date->toDateString() . ' ' . $time);
                    if ($slotTime->isFuture()) {
                        $availableSlots[] = $time;
                    }
                } else {
                    $availableSlots[] = $time;
                }
            }
        }
        
        return response()->json(['available_slots' => $availableSlots]);
    }

    /**
     * Получить список записей
     */
    public function getAppointments()
    {
        $appointments = Appointment::with(['client', 'employee', 'providedServices.service'])
            ->whereIn('status', [0, 1])
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date', 'asc')
            ->get()
            ->map(function($appointment) {
                return [
                    'id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'time' => Carbon::parse($appointment->date)->format('H:i'),
                    'client_name' => $appointment->client->client_name,
                    'doctor_name' => $appointment->employee->employee_name,
                    'service_name' => $appointment->services_list,
                    'status' => $appointment->status,
                ];
            });
        
        return response()->json(['appointments' => $appointments]);
    }
    
    /**
     * Страница управления складом (перенесено от бухгалтера)
     */
    public function warehouse()
    {
        $user = Auth::guard('employee')->user();
        
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
        
        $suppliers = Supplier::all()->map(function($supplier) {
            return [
                'supplier_id' => $supplier->supplier_id,
                'supplier_name' => $supplier->supplier_name,
            ];
        });
        
        $activeOrders = SupplierContract::where('status', SupplierContract::STATUS_CONFIRMED)
            ->with(['supplier', 'materialReceipts.material'])
            ->get()
            ->map(function($contract) {
                return [
                    'id' => $contract->contract_id,
                    'number' => $contract->number,
                    'supplier_name' => $contract->supplier?->supplier_name,
                    'total_amount' => $contract->materialReceipts->sum(function($receipt) {
                        return $receipt->quantity * $receipt->price;
                    }),
                    'items' => $contract->materialReceipts->map(function($receipt) {
                        return [
                            'material_name' => $receipt->material->name,
                            'quantity' => $receipt->quantity,
                            'price' => $receipt->price,
                            'unit' => $receipt->material->unit,
                        ];
                    }),
                ];
            });
        
        return Inertia::render('Admin/Warehouse', [
            'admin' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'materials' => $materials,
            'suppliers' => $suppliers,
            'activeOrders' => $activeOrders,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Принять заказ на склад
     */
    public function receiveOrder($orderId)
    {
        $order = SupplierContract::with(['materialReceipts.material'])
            ->findOrFail($orderId);
        
        if ($order->status != SupplierContract::STATUS_CONFIRMED) {
            return response()->json(['error' => 'Заказ не в статусе "В пути"'], 422);
        }
        
        DB::beginTransaction();
        
        try {
            foreach ($order->materialReceipts as $receipt) {
                if ($receipt->status != MaterialReceipt::STATUS_RECEIVED) {
                    $material = $receipt->material;
                    $material->current_balance += $receipt->quantity;
                    $material->save();
                    $receipt->status = MaterialReceipt::STATUS_RECEIVED;
                    $receipt->save();
                }
            }
            
            $order->status = SupplierContract::STATUS_RECEIVED;
            $order->received_at = now();
            $order->save();
            
            DB::commit();
            
            return response()->json(['success' => true, 'message' => 'Заказ принят на склад']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Получить материалы для склада с фильтрацией по поставщику
     */
    public function getWarehouseMaterials(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        
        if ($supplierId) {
            $supplier = Supplier::findOrFail($supplierId);
            $materials = $supplier->materials()
                ->orderBy('materials.name')
                ->get()
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
                        'supplier_price' => $material->pivot->price,
                        'status' => $status,
                    ];
                });
        } else {
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
        
        $material = Material::create([
            'name' => $request->name,
            'unit' => $request->unit,
            'min_stock' => $request->min_stock,
            'current_balance' => $request->current_balance,
            'price_per_unit' => $request->price_per_unit,
        ]);
        
        return response()->json(['success' => true, 'message' => 'Материал добавлен']);
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
        
        $material->update($request->all());
        
        return response()->json(['success' => true, 'message' => 'Материал обновлен']);
    }

    /**
     * Удалить материал
     */
    public function deleteMaterial($id)
    {
        $material = Material::findOrFail($id);
        $material->delete();
        
        return response()->json(['success' => true, 'message' => 'Материал удален']);
    }

    /**
     * Создать заказ на закупку
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
            $contractNumber = 'PO-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            $contract = SupplierContract::create([
                'number' => $contractNumber,
                'date' => now(),
                'status' => SupplierContract::STATUS_PENDING,
                'valid_from' => now(),
                'valid_to' => Carbon::now()->addMonths(1),
                'supplier_id' => $request->supplier_id,
            ]);
            
            foreach ($request->items as $item) {
                MaterialReceipt::create([
                    'quantity' => $item['quantity'],
                    'price' => $item['price_per_unit'],
                    'batch_number' => time(),
                    'expiry_date' => Carbon::now()->addYears(1),
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
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Получить список заказов
     */
    public function getOrders()
    {
        $orders = SupplierContract::with(['supplier', 'materialReceipts.material'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->contract_id,
                    'number' => $order->number,
                    'supplier_name' => $order->supplier?->supplier_name,
                    'status' => $order->status,
                    'status_text' => $order->status_text,
                    'total_amount' => $order->materialReceipts->sum(function($receipt) {
                        return $receipt->quantity * $receipt->price;
                    }),
                    'created_at' => $order->created_at,
                    'items' => $order->materialReceipts->map(function($receipt) {
                        return [
                            'material_name' => $receipt->material->name,
                            'quantity' => $receipt->quantity,
                            'price' => $receipt->price,
                            'unit' => $receipt->material->unit,
                        ];
                    }),
                ];
            });
        
        return response()->json($orders);
    }
    
    /**
     * Страница профиля
     */
    public function profile()
    {
        $user = Auth::guard('employee')->user();
        
        return Inertia::render('Admin/Profile', [
            'admin' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
}