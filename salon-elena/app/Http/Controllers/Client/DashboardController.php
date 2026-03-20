<?php
// app/Http/Controllers/Client/DashboardController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\MedicalRecord;
use App\Models\Feedback;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Главная страница с уведомлениями
     */
    public function index()
    {
        $client = Auth::guard('client')->user();
        
        // Получаем все записи клиента для статистики с правильной обработкой
        $appointments = Appointment::with(['employee', 'providedServices.service'])
            ->where('client_id', $client->client_id)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'status' => $appointment->status,
                    'employee' => $appointment->employee,
                    'provided_services' => $appointment->providedServices->map(function($ps) {
                        return [
                            'provided_id' => $ps->provided_id,
                            'quantity' => $ps->quantity,
                            'service' => $ps->service ? [
                                'service_id' => $ps->service->service_id,
                                'service_name' => $ps->service->service_name,
                                'service_category' => $ps->service->service_category,
                                'default_price' => $ps->service->default_price,
                            ] : null,
                        ];
                    }),
                    'service_names' => $appointment->services_list,
                ];
            });
        
        // Ближайшая запись
        $nextAppointment = Appointment::with(['employee', 'providedServices.service'])
            ->where('client_id', $client->client_id)
            ->where('date', '>=', now())
            ->whereIn('status', [0, 1])
            ->orderBy('date', 'asc')
            ->first();
        
        // Если есть ближайшая запись, обрабатываем её
        $nextAppointmentData = null;
        if ($nextAppointment) {
            $nextAppointmentData = [
                'appointment_id' => $nextAppointment->appointment_id,
                'date' => $nextAppointment->date,
                'status' => $nextAppointment->status,
                'employee' => $nextAppointment->employee,
                'provided_services' => $nextAppointment->providedServices->map(function($ps) {
                    return [
                        'provided_id' => $ps->provided_id,
                        'quantity' => $ps->quantity,
                        'service' => $ps->service ? [
                            'service_id' => $ps->service->service_id,
                            'service_name' => $ps->service->service_name,
                            'service_category' => $ps->service->service_category,
                            'default_price' => $ps->service->default_price,
                        ] : null,
                    ];
                }),
                'service_names' => $nextAppointment->services_list,
            ];
        }
        
        // Уведомления
        $notifications = [];
        
        // Напоминание о ближайшей записи
        if ($nextAppointment) {
            $daysUntil = Carbon::parse($nextAppointment->date)->diffInDays(now());
            if ($daysUntil <= 1) {
                $notifications[] = [
                    'id' => 1,
                    'type' => 'reminder',
                    'message' => "Напоминание: завтра запись к {$nextAppointment->employee->employee_name} в " . Carbon::parse($nextAppointment->date)->format('H:i'),
                    'created_at' => now(),
                ];
            }
        }
        
        // Напоминание об отзыве
        $completedWithoutFeedback = Appointment::with(['providedServices.service'])
            ->where('client_id', $client->client_id)
            ->where('status', 2)
            ->whereDoesntHave('feedback')
            ->latest('date')
            ->first();
        
        if ($completedWithoutFeedback) {
            $serviceName = null;
            if ($completedWithoutFeedback->providedServices && $completedWithoutFeedback->providedServices->count() > 0) {
                $firstService = $completedWithoutFeedback->providedServices->first();
                if ($firstService && $firstService->service) {
                    $serviceName = $firstService->service->service_name;
                }
            }
            
            $notifications[] = [
                'id' => 2,
                'type' => 'feedback',
                'message' => "Спасибо за визит! " . ($serviceName ? "Оставьте отзыв о услуге {$serviceName}" : "Оставьте отзыв о посещении"),
                'appointment_id' => $completedWithoutFeedback->appointment_id,
                'created_at' => now(),
            ];
        }
        
        // Акции
        $promotions = [
            [
                'id' => 1,
                'title' => 'Скидка 20% на комплексный уход',
                'description' => 'При записи на 3 процедуры',
                'image' => null,
            ],
            [
                'id' => 2,
                'title' => 'Новинка! PRP-терапия',
                'description' => 'Первое посещение со скидкой 15%',
                'image' => null,
            ],
        ];
        
        // Популярные услуги
        $popularServices = Service::where('is_active', 1)
            ->inRandomOrder()
            ->limit(3)
            ->get();
        
        return Inertia::render('Client/Dashboard', [
            'client' => [
                'client_id' => $client->client_id,
                'client_name' => $client->client_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'birth_date' => $client->birth_date,
                'photo' => $client->photo,
                'photo_url' => $client->photo ? Storage::url($client->photo) : null,
            ],
            'appointments' => $appointments,
            'nextAppointment' => $nextAppointmentData,
            'notifications' => $notifications,
            'promotions' => $promotions,
            'popularServices' => $popularServices,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница услуг (запись)
     */
    public function services()
    {
        $client = Auth::guard('client')->user();
        
        // Получаем услуги с привязанными врачами
        $services = Service::where('is_active', 1)
            ->with(['doctors' => function($query) {
                $query->select('employee_id', 'employee_name', 'role');
            }])
            ->orderBy('service_category')
            ->orderBy('service_name')
            ->get()
            ->groupBy('service_category');
        
        return Inertia::render('Client/Services', [
            'client' => [
                'client_id' => $client->client_id,
                'client_name' => $client->client_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'birth_date' => $client->birth_date,
                'photo' => $client->photo,
                'photo_url' => $client->photo ? Storage::url($client->photo) : null,
            ],
            'services' => $services,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }

    
    /**
     * Страница "Мои записи" (активные)
     */
    public function appointments()
    {
        $client = Auth::guard('client')->user();
        
        $appointments = Appointment::with(['employee', 'providedServices.service', 'feedback'])
            ->where('client_id', $client->client_id)
            ->whereIn('status', [0, 1])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'status' => $appointment->status,
                    'employee' => $appointment->employee,
                    'provided_services' => $appointment->providedServices->map(function($ps) {
                        return [
                            'provided_id' => $ps->provided_id,
                            'quantity' => $ps->quantity,
                            'service' => $ps->service ? [
                                'service_id' => $ps->service->service_id,
                                'service_name' => $ps->service->service_name,
                                'service_category' => $ps->service->service_category,
                                'default_price' => $ps->service->default_price,
                            ] : null,
                        ];
                    }),
                    'service_names' => $appointment->services_list,
                    'feedback' => $appointment->feedback,
                ];
            });
        
        return Inertia::render('Client/Appointments', [
            'client' => [
                'client_id' => $client->client_id,
                'client_name' => $client->client_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'birth_date' => $client->birth_date,
                'photo' => $client->photo,
                'photo_url' => $client->photo ? Storage::url($client->photo) : null,
            ],
            'appointments' => $appointments,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница "История посещений" (завершенные и отмененные)
     */
    public function history()
    {
        $client = Auth::guard('client')->user();
        
        $history = Appointment::with(['employee', 'providedServices.service', 'feedback'])
            ->where('client_id', $client->client_id)
            ->whereIn('status', [2, 3]) // Завершенные и отмененные
            ->orderBy('date', 'desc')
            ->get();
        
        return Inertia::render('Client/History', [
            'client' => [
                'client_id' => $client->client_id,
                'client_name' => $client->client_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'birth_date' => $client->birth_date,
                'photo' => $client->photo,
                'photo_url' => $client->photo ? Storage::url($client->photo) : null,
            ],
            'history' => $history,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница "Медицинская карта"
     */
    public function medicalRecords()
    {
        $client = Auth::guard('client')->user();
        
        $records = MedicalRecord::with('employee')
            ->where('client_id', $client->client_id)
            ->orderBy('visit_date', 'desc')
            ->get();
        
        return Inertia::render('Client/MedicalRecords', [
            'client' => [
                'client_id' => $client->client_id,
                'client_name' => $client->client_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'birth_date' => $client->birth_date,
                'photo' => $client->photo,
                'photo_url' => $client->photo ? Storage::url($client->photo) : null,
            ],
            'records' => $records,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница "Профиль"
     */
    public function profile()
    {
        $client = Auth::guard('client')->user();
        
        // Получаем статистику для профиля
        $appointments = Appointment::where('client_id', $client->client_id)->get();
        $feedback = Feedback::where('client_id', $client->client_id)->get();
        
        return Inertia::render('Client/Profile', [
            'client' => [
                'client_id' => $client->client_id,
                'client_name' => $client->client_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'birth_date' => $client->birth_date,
                'photo' => $client->photo,
                'photo_url' => $client->photo ? Storage::url($client->photo) : null,
            ],
            'appointments' => $appointments,
            'feedback' => $feedback,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    // ====================== API МЕТОДЫ ======================
    
    /**
     * Получить доступные слоты для записи
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|exists:services,service_id',
        ]);
        
        $service = Service::with('doctors')->find($request->service_id);
        
        if (!$service || $service->doctors->isEmpty()) {
            return response()->json(['error' => 'К данной услуге не привязан ни один врач'], 422);
        }
        
        $doctorId = $service->doctors->first()->employee_id;
        $date = Carbon::parse($request->date, 'Europe/Moscow');
        
        $workHours = range(9, 20);
        
        // Получаем занятые слоты
        $busySlots = Appointment::where('employee_id', $doctorId)
            ->whereDate('date', $date->toDateString())
            ->whereIn('status', [0, 1])
            ->get()
            ->map(function($appointment) {
                // Конвертируем время из UTC в московское для сравнения
                return Carbon::parse($appointment->date)->timezone('Europe/Moscow')->format('H:i');
            })
            ->toArray();
        
        $availableSlots = [];
        $now = Carbon::now('Europe/Moscow');
        $currentHour = $now->hour;
        
        foreach ($workHours as $hour) {
            $time = sprintf('%02d:00', $hour);
            
            // Проверяем, не занят ли слот и не прошел ли час
            if (!in_array($time, $busySlots)) {
                // Если сегодня и час уже прошел - пропускаем
                if ($date->isToday() && $hour <= $currentHour) {
                    continue;
                }
                $availableSlots[] = $time;
            }
        }
        
        return response()->json([
            'date' => $date->format('Y-m-d'),
            'doctor_name' => $service->doctors->first()->employee_name,
            'available_slots' => $availableSlots,
        ]);
    }
    
    /**
     * Создать новую запись
     */
    public function createAppointment(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $request->validate([
            'service_id' => 'required|exists:services,service_id',
            'date' => 'required|date|after:now',
            'time' => 'required|string',
        ]);
        
        $service = Service::with('doctors')->find($request->service_id);
        
        if (!$service || $service->doctors->isEmpty()) {
            return response()->json(['error' => 'К данной услуге не привязан ни один врач'], 422);
        }
        
        $doctorId = $service->doctors->first()->employee_id;
        
        // Создаем время в московском часовом поясе
        $dateTime = Carbon::parse($request->date . ' ' . $request->time, 'Europe/Moscow');
        
        // Проверяем, что выбранное время в будущем
        if ($dateTime->isPast()) {
            return response()->json(['error' => 'Выбранное время уже прошло'], 422);
        }
        
        // Проверяем, не занято ли время
        $exists = Appointment::where('employee_id', $doctorId)
            ->whereDate('date', $dateTime->toDateString())
            ->whereTime('date', $dateTime->toTimeString())
            ->whereIn('status', [0, 1])
            ->exists();
        
        if ($exists) {
            return response()->json(['error' => 'Это время уже занято'], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Сохраняем в UTC (Carbon автоматически сконвертирует)
            $appointment = Appointment::create([
                'date' => $dateTime, // Carbon сам сконвертирует в UTC при сохранении
                'status' => 0,
                'employee_id' => $doctorId,
                'client_id' => $client->client_id,
            ]);
            
            DB::table('provided_services')->insert([
                'quantity' => 1,
                'service_date' => $dateTime->toDateString(),
                'notes' => '',
                'appointment_id' => $appointment->appointment_id,
                'service_id' => $request->service_id,
                'employee_id' => $doctorId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Запись успешно создана',
                'appointment_id' => $appointment->appointment_id,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка при создании записи: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Отменить запись
     */
    public function cancelAppointment(Request $request, $id)
    {
        $client = Auth::guard('client')->user();
        
        $appointment = Appointment::where('appointment_id', $id)
            ->where('client_id', $client->client_id)
            ->firstOrFail();
        
        if (!in_array($appointment->status, [0, 1])) {
            return response()->json(['error' => 'Нельзя отменить эту запись'], 422);
        }
        
        $appointment->status = 3; // Отменен
        $appointment->save();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Перенести запись
     */
    public function rescheduleAppointment(Request $request, $id)
    {
        $client = Auth::guard('client')->user();
        
        $request->validate([
            'date' => 'required|date|after:now',
            'time' => 'required|string',
        ]);
        
        $appointment = Appointment::where('appointment_id', $id)
            ->where('client_id', $client->client_id)
            ->firstOrFail();
        
        if (!in_array($appointment->status, [0, 1])) {
            return response()->json(['error' => 'Нельзя перенести эту запись'], 422);
        }
        
        $dateTime = Carbon::parse($request->date . ' ' . $request->time);
        
        // Проверяем, не занято ли время
        $exists = Appointment::where('employee_id', $appointment->employee_id)
            ->where('appointment_id', '!=', $id)
            ->whereDate('date', $dateTime->toDateString())
            ->whereTime('date', $dateTime->toTimeString())
            ->whereIn('status', [0, 1])
            ->exists();
        
        if ($exists) {
            return response()->json(['error' => 'Это время уже занято'], 422);
        }
        
        $appointment->date = $dateTime;
        $appointment->save();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Оставить отзыв
     */
    public function leaveFeedback(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $request->validate([
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'score' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500',
        ]);
        
        // Проверяем, что запись принадлежит клиенту и завершена
        $appointment = Appointment::where('appointment_id', $request->appointment_id)
            ->where('client_id', $client->client_id)
            ->where('status', 2)
            ->firstOrFail();
        
        // Проверяем, нет ли уже отзыва
        if (Feedback::where('appointment_id', $request->appointment_id)->exists()) {
            return response()->json(['error' => 'Отзыв уже оставлен'], 422);
        }
        
        Feedback::create([
            'score' => $request->score,
            'comment' => $request->comment,
            'client_id' => $client->client_id,
            'appointment_id' => $request->appointment_id,
        ]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Обновить профиль
     */
    public function updateProfile(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $request->validate([
            'client_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $client->client_id . ',client_id',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
        ]);
        
        // Явно присваиваем значения полям
        $client->client_name = $request->client_name;
        $client->email = $request->email;
        
        // Преобразуем телефон в число (int), так как в БД поле phone имеет тип int
        if ($request->filled('phone')) {
            // Удаляем все нецифровые символы
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            $client->phone = (int)$phone;
        }
        
        if ($request->filled('birth_date')) {
            $client->birth_date = $request->birth_date;
        }
        
        $client->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Профиль успешно обновлен'
        ]);
    }
    
    // ====================== НОВЫЕ МЕТОДЫ ДЛЯ ФОТО ======================

    /**
     * Загрузка фото профиля
     */
    public function uploadPhoto(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // максимум 2MB
        ]);
        
        // Удаляем старое фото, если есть
        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
        }
        
        // Сохраняем новое фото
        $path = $request->file('photo')->store('clients', 'public');
        
        // Обновляем запись в БД
        $client->photo = $path;
        $client->save();
        
        return response()->json([
            'success' => true,
            'photo_url' => Storage::url($path),
            'message' => 'Фото успешно загружено'
        ]);
    }

    /**
     * Удаление фото
     */
    public function deletePhoto()
    {
        $client = Auth::guard('client')->user();
        
        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
            $client->photo = null;
            $client->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Фото удалено'
        ]);
    }
}