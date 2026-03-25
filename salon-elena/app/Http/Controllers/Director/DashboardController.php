<?php
// app/Http/Controllers/Director/DashboardController.php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Employee;
use App\Models\SupplierContract;
use App\Models\ClientContract;
use App\Models\Expense;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Главная страница директора
     */
    public function index()
    {
        $user = Auth::guard('employee')->user();
        
        // Получаем уведомления о новых поставках
        $pendingOrders = SupplierContract::where('status', SupplierContract::STATUS_PENDING)
            ->with('supplier')
            ->get()
            ->map(function($order) {
                return [
                    'id' => $order->contract_id,
                    'number' => $order->number,
                    'supplier_name' => $order->supplier->supplier_name,
                    'date' => $order->date,
                    'total_amount' => $order->materialReceipts->sum(function($receipt) {
                        return $receipt->quantity * $receipt->price;
                    }),
                ];
            });
        
        return Inertia::render('Director/Dashboard', [
            'director' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'pendingOrders' => $pendingOrders,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница управления услугами
     */
    public function services()
    {
        $user = Auth::guard('employee')->user();
        
        $services = Service::with('doctors')
            ->orderBy('service_category')
            ->orderBy('service_name')
            ->get()
            ->map(function($service) {
                return [
                    'service_id' => $service->service_id,
                    'service_name' => $service->service_name,
                    'service_category' => $service->service_category,
                    'default_price' => $service->default_price,
                    'is_active' => $service->is_active,
                    'doctors' => $service->doctors->map(function($doctor) {
                        return [
                            'employee_id' => $doctor->employee_id,
                            'employee_name' => $doctor->employee_name,
                        ];
                    }),
                ];
            });
        
        $doctors = Employee::where('role', 'doctor')
            ->orderBy('employee_name')
            ->get(['employee_id', 'employee_name']);
        
        $categories = Service::distinct()->pluck('service_category');
        
        return Inertia::render('Director/Services', [
            'director' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'services' => $services,
            'doctors' => $doctors,
            'categories' => $categories,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница управления персоналом
     */
    public function staff()
    {
        $user = Auth::guard('employee')->user();
        
        $employees = Employee::orderBy('role')
            ->orderBy('employee_name')
            ->get()
            ->map(function($employee) {
                return [
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $employee->employee_name,
                    'role' => $employee->role,
                    'role_text' => $this->getRoleText($employee->role),
                    'email' => $employee->email,
                    'employee_phone' => $employee->employee_phone,
                    'hourly_rate' => $employee->hourly_rate,
                    'photo' => $employee->photo,
                    'photo_url' => $employee->photo ? Storage::url($employee->photo) : null,
                    'created_at' => $employee->created_at,
                ];
            });
        
        $services = Service::where('is_active', 1)
            ->orderBy('service_name')
            ->get(['service_id', 'service_name', 'service_category']);
        
        return Inertia::render('Director/Staff', [
            'director' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'employees' => $employees,
            'services' => $services,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница контроля поставок
     */
    public function supplyControl()
    {
        $user = Auth::guard('employee')->user();
        
        $orders = SupplierContract::with(['supplier', 'materialReceipts.material'])
            ->orderBy('created_at', 'desc')
            ->get()
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
        
        return Inertia::render('Director/SupplyControl', [
            'director' => [
                'employee_id' => $user->employee_id,
                'employee_name' => $user->employee_name,
                'email' => $user->email,
                'employee_phone' => $user->employee_phone,
                'photo' => $user->photo,
                'photo_url' => $user->photo ? Storage::url($user->photo) : null,
                'role' => $user->role,
            ],
            'orders' => $orders,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница аналитики
     */
    public function analytics()
    {
        $user = Auth::guard('employee')->user();
        
        return Inertia::render('Director/Analytics', [
            'director' => [
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
    
    /**
     * Страница профиля
     */
    public function profile()
    {
        $user = Auth::guard('employee')->user();
        
        return Inertia::render('Director/Profile', [
            'director' => [
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
    
    // ====================== API МЕТОДЫ ======================
    
        /**
     * Создать новую услугу
     */
    public function createService(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string|max:100',
            'service_category' => 'required|string|max:100',
            'default_price' => 'required|numeric|min:0',
        ]);
        
        $service = Service::create([
            'service_name' => $request->service_name,
            'service_category' => $request->service_category,
            'default_price' => $request->default_price,
            'is_active' => 1,
        ]);
        
        // Загружаем созданную услугу с врачами (пока пустой список)
        $createdService = Service::with('doctors')->find($service->service_id);
        
        return response()->json([
            'success' => true,
            'message' => 'Услуга добавлена',
            'service' => [
                'service_id' => $createdService->service_id,
                'service_name' => $createdService->service_name,
                'service_category' => $createdService->service_category,
                'default_price' => $createdService->default_price,
                'is_active' => $createdService->is_active,
                'doctors' => [],
            ],
        ]);
    }
    
        /**
     * Обновить услугу
     */
    public function updateService(Request $request, $id)
    {
        $service = Service::findOrFail($id);
        
        $request->validate([
            'service_name' => 'required|string|max:100',
            'service_category' => 'required|string|max:100',
            'default_price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean', // валидация принимает true/false
        ]);
        
        $service->update([
            'service_name' => $request->service_name,
            'service_category' => $request->service_category,
            'default_price' => $request->default_price,
            'is_active' => $request->is_active ? 1 : 0, // Преобразуем boolean в int
        ]);
        
        // Загружаем обновленные данные с врачами
        $updatedService = Service::with('doctors')->find($id);
        
        return response()->json([
            'success' => true,
            'message' => 'Услуга обновлена',
            'service' => [
                'service_id' => $updatedService->service_id,
                'service_name' => $updatedService->service_name,
                'service_category' => $updatedService->service_category,
                'default_price' => $updatedService->default_price,
                'is_active' => (bool) $updatedService->is_active, // Возвращаем как boolean для удобства
                'doctors' => $updatedService->doctors->map(function($doctor) {
                    return [
                        'employee_id' => $doctor->employee_id,
                        'employee_name' => $doctor->employee_name,
                    ];
                }),
            ],
        ]);
    }
    
    /**
     * Удалить услугу
     */
    public function deleteService($id)
    {
        $service = Service::findOrFail($id);
        
        // Проверяем, есть ли связанные записи
        if ($service->providedServices()->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Нельзя удалить услугу, так как она уже использовалась в приемах'
            ], 422);
        }
        
        $service->doctors()->detach();
        $service->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Услуга удалена',
        ]);
    }
    
    /**
     * Назначить услугу врачу
     */
    public function assignServiceToDoctor(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:employees,employee_id',
            'service_id' => 'required|exists:services,service_id',
        ]);
        
        $doctor = Employee::findOrFail($request->doctor_id);
        
        if (!$doctor->isDoctor()) {
            return response()->json([
                'success' => false,
                'error' => 'Сотрудник не является врачом'
            ], 422);
        }
        
        $doctor->services()->syncWithoutDetaching([$request->service_id]);
        
        return response()->json([
            'success' => true,
            'message' => 'Услуга назначена врачу',
        ]);
    }
    
    /**
     * Открепить услугу от врача
     */
    public function detachServiceFromDoctor(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:employees,employee_id',
            'service_id' => 'required|exists:services,service_id',
        ]);
        
        $doctor = Employee::findOrFail($request->doctor_id);
        $doctor->services()->detach($request->service_id);
        
        return response()->json([
            'success' => true,
            'message' => 'Услуга откреплена от врача',
        ]);
    }
    
    /**
     * Создать нового сотрудника
     */
    public function createEmployee(Request $request)
    {
        $request->validate([
            'employee_name' => 'required|string|max:100',
            'role' => 'required|in:doctor,admin,director,accountant',
            'email' => 'required|email|unique:employees,email',
            'login' => 'required|string|max:56|unique:employees,login',
            'employee_phone' => 'nullable|string|max:20',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);
        
        $employee = Employee::create([
            'employee_name' => $request->employee_name,
            'role' => $request->role,
            'email' => $request->email,
            'login' => $request->login,
            'employee_phone' => $request->employee_phone,
            'hourly_rate' => $request->hourly_rate,
            'passwd' => bcrypt('password123'), // Временный пароль
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Сотрудник добавлен',
            'employee' => $employee,
            'temp_password' => 'password123', // В реальном проекте отправлять по email
        ]);
    }
    
    /**
     * Обновить сотрудника
     */
    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        
        $request->validate([
            'employee_name' => 'required|string|max:100',
            'role' => 'required|in:doctor,admin,director,accountant',
            'email' => 'required|email|unique:employees,email,' . $id . ',employee_id',
            'employee_phone' => 'nullable|string|max:20',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);
        
        $employee->update([
            'employee_name' => $request->employee_name,
            'role' => $request->role,
            'email' => $request->email,
            'employee_phone' => $request->employee_phone,
            'hourly_rate' => $request->hourly_rate,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Сотрудник обновлен',
            'employee' => $employee,
        ]);
    }
    
    /**
     * Удалить сотрудника
     */
    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        
        // Нельзя удалить самого себя
        if ($employee->employee_id == Auth::guard('employee')->user()->employee_id) {
            return response()->json([
                'success' => false,
                'error' => 'Нельзя удалить свой аккаунт'
            ], 422);
        }
        
        // Проверяем, есть ли у сотрудника записи
        if ($employee->appointments()->exists() || $employee->providedServices()->exists()) {
            return response()->json([
                'success' => false,
                'error' => 'Нельзя удалить сотрудника, у которого есть записи'
            ], 422);
        }
        
        $employee->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Сотрудник удален',
        ]);
    }
    
    /**
     * Подтвердить заказ (для директора)
     */
    public function confirmOrder($orderId)
    {
        $order = SupplierContract::findOrFail($orderId);
        
        if ($order->status != SupplierContract::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'error' => 'Заказ уже обработан'
            ], 422);
        }
        
        $order->status = SupplierContract::STATUS_CONFIRMED;
        $order->confirmed_at = now();
        $order->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Заказ подтвержден',
        ]);
    }
    
    /**
     * Отклонить заказ
     */
    public function rejectOrder($orderId)
    {
        $order = SupplierContract::findOrFail($orderId);
        
        if ($order->status != SupplierContract::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'error' => 'Заказ уже обработан'
            ], 422);
        }
        
        $order->status = SupplierContract::STATUS_CANCELLED;
        $order->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Заказ отклонен',
        ]);
    }
    
    /**
     * Получить данные для аналитики
     */
    public function getAnalyticsData(Request $request)
    {
        $period = $request->get('period', 'all'); // all, month, year
        $month = $request->get('month');
        $year = $request->get('year', date('Y'));
        
        $query = ClientContract::with(['appointment.providedServices.service', 'appointment.employee']);
        
        if ($period === 'month' && $month) {
            $query->whereMonth('created_at', $month)
                  ->whereYear('created_at', $year);
        } elseif ($period === 'year') {
            $query->whereYear('created_at', $year);
        }
        
        $contracts = $query->get();
        
        // Средний чек
        $averageCheck = $contracts->avg('total_amount') ?? 0;
        
        // Самая популярная услуга
        $popularServices = [];
        foreach ($contracts as $contract) {
            if ($contract->appointment) {
                foreach ($contract->appointment->providedServices as $ps) {
                    if ($ps->service) {
                        $serviceName = $ps->service->service_name;
                        if (!isset($popularServices[$serviceName])) {
                            $popularServices[$serviceName] = 0;
                        }
                        $popularServices[$serviceName] += $ps->quantity;
                    }
                }
            }
        }
        arsort($popularServices);
        $mostPopularService = key($popularServices) ?: 'Нет данных';
        $mostPopularCount = current($popularServices) ?: 0;
        
        // Эффективность сотрудников (по сумме услуг)
        $employeeEfficiency = [];
        foreach ($contracts as $contract) {
            if ($contract->appointment && $contract->appointment->employee) {
                $employeeName = $contract->appointment->employee->employee_name;
                if (!isset($employeeEfficiency[$employeeName])) {
                    $employeeEfficiency[$employeeName] = [
                        'total_amount' => 0,
                        'appointments_count' => 0,
                    ];
                }
                $employeeEfficiency[$employeeName]['total_amount'] += $contract->total_amount;
                $employeeEfficiency[$employeeName]['appointments_count']++;
            }
        }
        
        // Доходы и расходы по месяцам
        $financialData = [];
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $monthKey = $current->format('Y-m');
            $monthName = $current->translatedFormat('M Y');
            
            $income = ClientContract::whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month)
                ->sum('total_amount');
            
            $expense = Expense::whereYear('date', $current->year)
                ->whereMonth('date', $current->month)
                ->sum('amount');
            
            $financialData[] = [
                'month' => $monthKey,
                'month_name' => $monthName,
                'income' => $income,
                'expense' => $expense,
                'profit' => $income - $expense,
            ];
            
            $current->addMonth();
        }
        
        // Рейтинг салона
        $averageRating = Feedback::avg('score') ?? 0;
        $totalReviews = Feedback::count();
        
        return response()->json([
            'average_check' => $averageCheck,
            'most_popular_service' => [
                'name' => $mostPopularService,
                'count' => $mostPopularCount,
            ],
            'employee_efficiency' => $employeeEfficiency,
            'financial_data' => $financialData,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
        ]);
    }
        /**
     * Получить список сотрудников для страницы управления персоналом
     */
    public function getStaffList(Request $request)
    {
        $role = $request->get('role');
        
        $query = Employee::query();
        
        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }
        
        $employees = $query->orderBy('role')
            ->orderBy('employee_name')
            ->get()
            ->map(function($employee) {
                // Получаем услуги для врача
                $services = [];
                if ($employee->role === 'doctor') {
                    $services = $employee->services()->get()->map(function($service) {
                        return [
                            'service_id' => $service->service_id,
                            'service_name' => $service->service_name,
                            'service_category' => $service->service_category,
                        ];
                    });
                }
                
                return [
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $employee->employee_name,
                    'role' => $employee->role,
                    'role_text' => $this->getRoleText($employee->role),
                    'email' => $employee->email,
                    'employee_phone' => $employee->employee_phone,
                    'hourly_rate' => $employee->hourly_rate,
                    'photo' => $employee->photo,
                    'photo_url' => $employee->photo ? Storage::url($employee->photo) : null,
                    'created_at' => $employee->created_at,
                    'services' => $services,
                ];
            });
        
        return response()->json([
            'employees' => $employees,
        ]);
    }

    /**
     * Получить список всех услуг для назначения врачам
     */
    public function getServicesList()
    {
        $services = Service::where('is_active', 1)
            ->orderBy('service_category')
            ->orderBy('service_name')
            ->get()
            ->map(function($service) {
                return [
                    'service_id' => $service->service_id,
                    'service_name' => $service->service_name,
                    'service_category' => $service->service_category,
                    'default_price' => $service->default_price,
                ];
            });
        
        return response()->json([
            'services' => $services,
        ]);
    }

    /**
     * Получить услуги врача
     */
    public function getDoctorServices($doctorId)
    {
        $doctor = Employee::findOrFail($doctorId);
        
        $services = $doctor->services()->get()->map(function($service) {
            return [
                'service_id' => $service->service_id,
                'service_name' => $service->service_name,
                'service_category' => $service->service_category,
                'default_price' => $service->default_price,
            ];
        });
        
        return response()->json([
            'services' => $services,
        ]);
    }

    /**
     * Получить расписание сотрудника
     */
    public function getEmployeeSchedule($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        
        $schedule = [];
        $days = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            0 => 'Воскресенье'
        ];
        
        foreach ($days as $dayNum => $dayName) {
            $daySchedule = $employee->getScheduleForDay($dayNum);
            $schedule[] = [
                'day' => $dayNum,
                'day_name' => $dayName,
                'start_time' => $daySchedule && $daySchedule->start_time ? Carbon::parse($daySchedule->start_time)->format('H:i') : null,
                'end_time' => $daySchedule && $daySchedule->end_time ? Carbon::parse($daySchedule->end_time)->format('H:i') : null,
                'slot_duration' => $daySchedule ? $daySchedule->slot_duration : 60,
                'working' => $daySchedule && $daySchedule->start_time && $daySchedule->end_time,
            ];
        }
        
        return response()->json([
            'employee' => [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->employee_name,
                'role' => $employee->role,
            ],
            'schedule' => $schedule,
        ]);
    }

    /**
     * Сохранить расписание сотрудника
     */
    public function saveEmployeeSchedule(Request $request, $employeeId)
    {
        $request->validate([
            'schedule' => 'required|array',
            'schedule.*.day' => 'required|integer|min:0|max:6',
            'schedule.*.working' => 'required|boolean',
            'schedule.*.start_time' => 'nullable|required_if:schedule.*.working,true|date_format:H:i',
            'schedule.*.end_time' => 'nullable|required_if:schedule.*.working,true|date_format:H:i|after:schedule.*.start_time',
            'schedule.*.slot_duration' => 'nullable|integer|min:15|max:120',
        ]);
        
        $employee = Employee::findOrFail($employeeId);
        
        DB::beginTransaction();
        
        try {
            foreach ($request->schedule as $dayData) {
                if ($dayData['working']) {
                    $employee->setSchedule(
                        $dayData['day'],
                        $dayData['start_time'],
                        $dayData['end_time'],
                        $dayData['slot_duration'] ?? 60
                    );
                } else {
                    // Удаляем расписание на этот день
                    $employee->schedule()->where('day_of_week', $dayData['day'])->delete();
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Расписание сохранено',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при сохранении расписания: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Вспомогательный метод для получения текста роли
     */
    private function getRoleText($role)
    {
        return match($role) {
            'doctor' => 'Врач',
            'admin' => 'Администратор',
            'director' => 'Директор',
            'accountant' => 'Бухгалтер',
            default => $role,
        };
    }
    
    /**
     * Обновить профиль директора
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