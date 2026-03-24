<?php
// app/Http/Controllers/Doctor/DashboardController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Material;
use App\Models\Service;
use App\Models\MedicalRecord;
use App\Models\ClientContract;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Notification;

class DashboardController extends Controller
{
    /**
     * Главная страница с графиком
     */
    public function index()
    {
        $doctor = Auth::guard('employee')->user();
        
        if ($doctor->role !== 'doctor') {
            abort(403, 'Доступ запрещен');
        }
        
        $appointments = Appointment::with(['client', 'providedServices.service', 'materials'])
            ->where('employee_id', $doctor->employee_id)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'status' => $appointment->status,
                    'client' => $appointment->client ? [
                        'client_id' => $appointment->client->client_id,
                        'client_name' => $appointment->client->client_name,
                        'phone' => $appointment->client->phone,
                        'photo' => $appointment->client->photo,
                        'photo_url' => $appointment->client->photo ? Storage::url($appointment->client->photo) : null,
                    ] : null,
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
                    'materials' => $appointment->materials,
                ];
            });
        
        $patients = Client::whereIn('client_id', function($query) use ($doctor) {
                $query->select('client_id')
                    ->from('appointments')
                    ->where('employee_id', $doctor->employee_id)
                    ->distinct();
            })
            ->get()
            ->map(function ($patient) {
                $lastAppointment = Appointment::where('client_id', $patient->client_id)
                    ->where('status', 2)
                    ->latest('date')
                    ->first();
                
                return [
                    'client_id' => $patient->client_id,
                    'client_name' => $patient->client_name,
                    'phone' => $patient->phone,
                    'photo' => $patient->photo,
                    'photo_url' => $patient->photo ? Storage::url($patient->photo) : null,
                    'birth_date' => $patient->birth_date ? Carbon::parse($patient->birth_date)->format('d.m.Y') : '—',
                    'last_visit' => $lastAppointment ? Carbon::parse($lastAppointment->date)->format('d.m.Y') : 'Нет',
                ];
            });
        
        $services = Service::where('is_active', 1)
            ->with(['materials' => function($query) {
                $query->withPivot('quantity');
            }])
            ->get();
        
        $materials = Material::where('current_balance', '<=', DB::raw('min_stock'))
            ->get();
        
        return Inertia::render('Doctor/Dashboard', [
            'doctor' => [
                'employee_id' => $doctor->employee_id,
                'employee_name' => $doctor->employee_name,
                'email' => $doctor->email,
                'employee_phone' => $doctor->employee_phone,
                'photo' => $doctor->photo,
                'photo_url' => $doctor->photo ? Storage::url($doctor->photo) : null,
                'role' => $doctor->role,
            ],
            'appointments' => $appointments,
            'patients' => $patients,
            'services' => $services,
            'materials' => $materials,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница "Мои пациенты"
     */
    public function patients()
    {
        $doctor = Auth::guard('employee')->user();
        
        $patients = Client::whereIn('client_id', function($query) use ($doctor) {
                $query->select('client_id')
                    ->from('appointments')
                    ->where('employee_id', $doctor->employee_id)
                    ->distinct();
            })
            ->get()
            ->map(function ($patient) {
                $lastAppointment = Appointment::where('client_id', $patient->client_id)
                    ->where('status', 2)
                    ->latest('date')
                    ->first();
                
                return [
                    'client_id' => $patient->client_id,
                    'client_name' => $patient->client_name,
                    'phone' => $patient->phone,
                    'photo' => $patient->photo,
                    'photo_url' => $patient->photo ? Storage::url($patient->photo) : null,
                    'birth_date' => $patient->birth_date ? Carbon::parse($patient->birth_date)->format('d.m.Y') : '—',
                    'last_visit' => $lastAppointment ? Carbon::parse($lastAppointment->date)->format('d.m.Y') : 'Нет',
                ];
            });
        
        return Inertia::render('Doctor/Patients', [
            'doctor' => [
                'employee_id' => $doctor->employee_id,
                'employee_name' => $doctor->employee_name,
                'email' => $doctor->email,
                'employee_phone' => $doctor->employee_phone,
                'photo' => $doctor->photo,
                'photo_url' => $doctor->photo ? Storage::url($doctor->photo) : null,
                'role' => $doctor->role,
            ],
            'patients' => $patients,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница "Мои услуги" - показывает только услуги, закрепленные за врачом
     */
    public function services()
    {
        $doctor = Auth::guard('employee')->user();
        
        $services = $doctor->services()
            ->with(['materials' => function($query) {
                $query->withPivot('quantity');
            }])
            ->get();
        
        return Inertia::render('Doctor/Services', [
            'doctor' => [
                'employee_id' => $doctor->employee_id,
                'employee_name' => $doctor->employee_name,
                'email' => $doctor->email,
                'employee_phone' => $doctor->employee_phone,
                'photo' => $doctor->photo,
                'photo_url' => $doctor->photo ? Storage::url($doctor->photo) : null,
                'role' => $doctor->role,
            ],
            'services' => $services,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница "Профиль"
     */
    public function profile()
    {
        $doctor = Auth::guard('employee')->user();
        
        return Inertia::render('Doctor/Profile', [
            'doctor' => [
                'employee_id' => $doctor->employee_id,
                'employee_name' => $doctor->employee_name,
                'email' => $doctor->email,
                'employee_phone' => $doctor->employee_phone,
                'photo' => $doctor->photo,
                'photo_url' => $doctor->photo ? Storage::url($doctor->photo) : null,
                'role' => $doctor->role,
            ],
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница медицинской карты пациента
     */
    public function medicalRecord($clientId)
    {
        $doctor = Auth::guard('employee')->user();
        
        $patient = Client::findOrFail($clientId);
        
        $patientData = [
            'client_id' => $patient->client_id,
            'client_name' => $patient->client_name,
            'phone' => $patient->phone,
            'birth_date' => $patient->birth_date,
            'photo' => $patient->photo,
            'photo_url' => $patient->photo ? Storage::url($patient->photo) : null,
        ];
        
        $records = MedicalRecord::with(['employee', 'appointment.providedServices.service'])
            ->where('client_id', $clientId)
            ->orderBy('visit_date', 'desc')
            ->get();
        
        $appointments = Appointment::with(['providedServices.service', 'medicalRecord'])
            ->where('client_id', $clientId)
            ->where('employee_id', $doctor->employee_id)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($appointment) {
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->date,
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
                    'medical_record' => $appointment->medicalRecord,
                ];
            });
        
        return Inertia::render('Doctor/MedicalRecord', [
            'doctor' => [
                'employee_id' => $doctor->employee_id,
                'employee_name' => $doctor->employee_name,
                'email' => $doctor->email,
                'employee_phone' => $doctor->employee_phone,
                'photo' => $doctor->photo,
                'photo_url' => $doctor->photo ? Storage::url($doctor->photo) : null,
                'role' => $doctor->role,
            ],
            'patient' => $patientData,
            'records' => $records,
            'appointments' => $appointments,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Сохранить новую запись в медицинской карте
     */
    public function saveMedicalRecord(Request $request, $clientId)
    {
        $doctor = Auth::guard('employee')->user();
        
        $request->validate([
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'visit_date' => 'required|date',
            'anamnesis' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'contraindications' => 'nullable|string',
        ]);
        
        $appointment = Appointment::where('appointment_id', $request->appointment_id)
            ->where('employee_id', $doctor->employee_id)
            ->where('client_id', $clientId)
            ->firstOrFail();
        
        $existingRecord = MedicalRecord::where('appointment_id', $request->appointment_id)->first();
        if ($existingRecord) {
            return response()->json(['error' => 'Запись для этого приема уже существует'], 422);
        }
        
        $record = MedicalRecord::create([
            'visit_date' => $request->visit_date,
            'anamnesis' => $request->anamnesis,
            'diagnosis' => $request->diagnosis,
            'contraindications' => $request->contraindications,
            'employee_id' => $doctor->employee_id,
            'client_id' => $clientId,
            'appointment_id' => $request->appointment_id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Запись сохранена',
            'record' => $record->load('employee')
        ]);
    }
    
    /**
     * Обновить существующую запись в медицинской карте
     */
    public function updateMedicalRecord(Request $request, $recordId)
    {
        $doctor = Auth::guard('employee')->user();
        
        $record = MedicalRecord::with('appointment')
            ->where('record_id', $recordId)
            ->where('employee_id', $doctor->employee_id)
            ->firstOrFail();
        
        $request->validate([
            'anamnesis' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'contraindications' => 'nullable|string',
        ]);
        
        $record->update([
            'anamnesis' => $request->anamnesis,
            'diagnosis' => $request->diagnosis,
            'contraindications' => $request->contraindications,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Запись обновлена',
            'record' => $record->load('employee')
        ]);
    }
    
    // ====================== НОВЫЕ МЕТОДЫ ДЛЯ ФОТО ======================
    
    /**
     * Загрузка фото профиля
     */
    public function uploadPhoto(Request $request)
    {
        $doctor = Auth::guard('employee')->user();
        
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($doctor->photo) {
            Storage::disk('public')->delete($doctor->photo);
        }
        
        $path = $request->file('photo')->store('doctors', 'public');
        
        $doctor->photo = $path;
        $doctor->save();
        
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
        $doctor = Auth::guard('employee')->user();
        
        if ($doctor->photo) {
            Storage::disk('public')->delete($doctor->photo);
            $doctor->photo = null;
            $doctor->save();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Фото удалено'
        ]);
    }
    




    
    // ====================== ОСТАЛЬНЫЕ API МЕТОДЫ ======================
    
    /**
     * Получить список приемов для календаря
     */
    public function getAppointments(Request $request)
    {
        $doctor = Auth::guard('employee')->user();
        $view = $request->get('view', 'week');
        $date = $request->get('date', now()->toDateString());
        
        $targetDate = Carbon::parse($date, 'Europe/Moscow');
        
        if ($view === 'day') {
            $start = $targetDate->copy()->startOfDay()->utc();
            $end = $targetDate->copy()->endOfDay()->utc();
        } else {
            $start = $targetDate->copy()->startOfWeek()->utc();
            $end = $targetDate->copy()->endOfWeek()->utc();
        }
        
        // ИСПРАВЛЕНО: убрал .material, так как materials уже загружает материалы
        $appointments = Appointment::with(['client', 'providedServices.service', 'materials'])
            ->where('employee_id', $doctor->employee_id)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get()
            ->map(function($appointment) {
                $localTime = Carbon::parse($appointment->date)->timezone('Europe/Moscow');
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'date' => $appointment->date,
                    'local_date' => $localTime->toDateString(),
                    'local_time' => $localTime->format('H:i'),
                    'status' => $appointment->status,
                    'client' => $appointment->client ? [
                        'client_id' => $appointment->client->client_id,
                        'client_name' => $appointment->client->client_name,
                        'phone' => $appointment->client->phone,
                        'photo' => $appointment->client->photo,
                        'photo_url' => $appointment->client->photo ? Storage::url($appointment->client->photo) : null,
                    ] : null,
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
                    'materials' => $appointment->materials->map(function($material) {
                        return [
                            'material_id' => $material->material_id,
                            'name' => $material->name,
                            'quantity_used' => $material->pivot->quantity_used,
                            'unit' => $material->unit,
                        ];
                    }),
                ];
            });
        
        // Получаем расписание на неделю для отображения рабочих часов
        $schedule = [];
        for ($i = 0; $i < 7; $i++) {
            $daySchedule = $doctor->getScheduleForDay($i);
            if ($daySchedule && $daySchedule->start_time && $daySchedule->end_time) {
                $schedule[$i] = [
                    'start' => Carbon::parse($daySchedule->start_time)->format('H:i'),
                    'end' => Carbon::parse($daySchedule->end_time)->format('H:i'),
                    'working' => true
                ];
            } else {
                $schedule[$i] = ['working' => false];
            }
        }
        
        return response()->json([
            'appointments' => $appointments,
            'schedule' => $schedule
        ]);
    }
    
    /**
     * Получить детали приема для модального окна
     */
    public function getAppointment($id)
    {
        // ИСПРАВЛЕНО: убрал .material
        $appointment = Appointment::with(['client', 'providedServices.service', 'materials', 'clientContract'])
            ->findOrFail($id);
        
        $localTime = Carbon::parse($appointment->date)->timezone('Europe/Moscow');
        
        $data = [
            'appointment_id' => $appointment->appointment_id,
            'date' => $appointment->date,
            'local_date' => $localTime->toDateString(),
            'local_time' => $localTime->format('H:i'),
            'status' => $appointment->status,
            'total_price' => $appointment->total_price, // ДОБАВЛЕНО
            'contract_number' => $appointment->clientContract?->contract_number, // ДОБАВЛЕНО
            'client' => $appointment->client ? [
                'client_id' => $appointment->client->client_id,
                'client_name' => $appointment->client->client_name,
                'phone' => $appointment->client->phone,
                'photo' => $appointment->client->photo,
                'photo_url' => $appointment->client->photo ? Storage::url($appointment->client->photo) : null,
            ] : null,
            'provided_services' => $appointment->providedServices->map(function($ps) {
                return [
                    'provided_id' => $ps->provided_id,
                    'quantity' => $ps->quantity,
                    'notes' => $ps->notes,
                    'service' => $ps->service ? [
                        'service_id' => $ps->service->service_id,
                        'service_name' => $ps->service->service_name,
                        'service_category' => $ps->service->service_category,
                        'default_price' => $ps->service->default_price,
                    ] : null,
                ];
            }),
            'materials' => $appointment->materials->map(function($material) {
                return [
                    'consumption_id' => $material->pivot->id ?? null,
                    'quantity' => $material->pivot->quantity_used,
                    'cost_price' => $material->pivot->cost_price, // ДОБАВЛЕНО для отображения цены
                    'material' => [
                        'material_id' => $material->material_id,
                        'name' => $material->name,
                        'unit' => $material->unit,
                    ],
                ];
            }),
            'service_name' => $appointment->service_name,
            'services_list' => $appointment->services_list,
        ];
        
        return response()->json($data);
    }
    
    /**
     * Обновить статус приема (старый метод, оставляем для совместимости, но ограничиваем)
     */
    public function updateStatus(Request $request, $id)
    {
        $doctor = Auth::guard('employee')->user();
        $appointment = Appointment::where('appointment_id', $id)
            ->where('employee_id', $doctor->employee_id)
            ->firstOrFail();
        
        // Запрещаем изменение статуса на завершенный через этот метод
        if ($request->status == Appointment::STATUS_COMPLETED) {
            return response()->json(['error' => 'Используйте метод completeAppointment для завершения'], 422);
        }
        
        $appointment->status = $request->status;
        $appointment->save();
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Сохранить материалы приема
     */
    public function saveAppointmentMaterials(Request $request, $id)
    {
        $request->validate([
            'materials' => 'required|array',
            'materials.*.material_id' => 'required|exists:materials,material_id',
            'materials.*.quantity_used' => 'required|integer|min:1',
            'materials.*.notes' => 'nullable|string|max:255',
        ]);

        $appointment = Appointment::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            foreach ($request->materials as $materialData) {
                $material = Material::find($materialData['material_id']);
                
                if (!$material) {
                    throw new \Exception("Материал не найден");
                }
                
                if ($material->current_balance < $materialData['quantity_used']) {
                    throw new \Exception("Недостаточно {$material->name} на складе. Доступно: {$material->current_balance} {$material->unit}");
                }

                // Уменьшаем остаток на складе
                $material->current_balance -= $materialData['quantity_used'];
                $material->save();

                // Сохраняем в appointment_materials с ценой на момент использования
                $appointment->materials()->attach($materialData['material_id'], [
                    'quantity_used' => $materialData['quantity_used'],
                    'cost_price' => $material->price_per_unit ?? 0,
                    'notes' => $materialData['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Записываем в consumption для учета
                DB::table('consumption')->insert([
                    'batch_number' => 'APT-' . $id . '-' . time() . '-' . $material->material_id,
                    'quantity' => $materialData['quantity_used'],
                    'cost_price' => $material->price_per_unit ?? 0,
                    'provided_id' => null,
                    'material_id' => $materialData['material_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();
            
            // Пересчитываем общую сумму после добавления материалов
            $totalPrice = $appointment->calculateTotalPrice();
            
            return response()->json([
                'success' => true, 
                'message' => 'Материалы успешно сохранены',
                'total_price' => $totalPrice
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    
    /**
     * Добавить один материал (старый метод для совместимости)
     */
    public function addMaterial(Request $request, $id)
    {
        $request->validate([
            'material_id' => 'required|exists:materials,material_id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        DB::transaction(function() use ($request, $id) {
            $material = Material::find($request->material_id);
            
            if ($material->current_balance < $request->quantity) {
                throw new \Exception('Недостаточно материалов');
            }
            
            $material->current_balance -= $request->quantity;
            $material->save();
            
            $appointment = Appointment::find($id);
            $appointment->materials()->attach($request->material_id, [
                'quantity_used' => $request->quantity,
                'cost_price' => 0,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('consumption')->insert([
                'batch_number' => 'AUTO-' . time(),
                'quantity' => $request->quantity,
                'cost_price' => 0,
                'provided_id' => null,
                'material_id' => $request->material_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Подтвердить или отклонить запись
     */
    public function confirmAppointment(Request $request, $id)
    {
        $doctor = Auth::guard('employee')->user();
        
        $request->validate([
            'status' => 'required|in:1,3', // 1 - подтвердить, 3 - отклонить
            'reason' => 'nullable|string|max:255', // причина отклонения
        ]);
        
        $appointment = Appointment::with(['client', 'providedServices.service'])
            ->where('appointment_id', $id)
            ->where('employee_id', $doctor->employee_id)
            ->firstOrFail();
        
        // Проверяем, что запись ожидает подтверждения
        if ($appointment->status != Appointment::STATUS_PENDING) {
            return response()->json(['error' => 'Запись уже обработана'], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Обновляем статус
            $appointment->status = $request->status;
            $appointment->save();
            
            // Формируем сообщение для клиента
            $formattedDate = Carbon::parse($appointment->date)->timezone('Europe/Moscow')->format('d.m.Y H:i');
            $serviceName = $appointment->services_list;
            
            if ($request->status == Appointment::STATUS_CONFIRMED) {
                $message = "✅ Ваша запись на {$formattedDate} (услуга: {$serviceName}) подтверждена! Ждем вас в салоне.";
            } else {
                $message = "❌ Ваша запись на {$formattedDate} (услуга: {$serviceName}) отклонена. Причина: " . ($request->reason ?? 'не указана');
            }
            
            // Создаем уведомление для клиента
            Notification::create([
                'client_id' => $appointment->client_id,
                'appointment_id' => $appointment->appointment_id,
                'type' => $request->status == Appointment::STATUS_CONFIRMED ? 'confirmation' : 'rejection',
                'message' => $message,
                'is_read' => false,
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $request->status == Appointment::STATUS_CONFIRMED ? 'Запись подтверждена' : 'Запись отклонена',
                'appointment' => [
                    'id' => $appointment->appointment_id,
                    'status' => $appointment->status,
                ],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Confirm appointment error: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Завершить прием и создать чек
     */
    public function completeAppointment(Request $request, $id)
    {
        $doctor = Auth::guard('employee')->user();
        
        // Загружаем услуги и материалы для расчета
        $appointment = Appointment::with(['providedServices.service', 'materials', 'client'])
            ->where('appointment_id', $id)
            ->where('employee_id', $doctor->employee_id)
            ->firstOrFail();
        
        // Проверяем, что запись подтверждена
        if ($appointment->status != Appointment::STATUS_CONFIRMED) {
            return response()->json(['error' => 'Прием должен быть подтвержден перед завершением'], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Рассчитываем итоговую стоимость
            $totalPrice = $appointment->calculateTotalPrice();
            
            // Обновляем статус и сумму
            $appointment->status = Appointment::STATUS_COMPLETED;
            $appointment->total_price = $totalPrice;
            $appointment->save();
            
            // Генерируем номер контракта
            $contractNumber = 'INV-' . date('Ymd') . '-' . str_pad($appointment->appointment_id, 6, '0', STR_PAD_LEFT);
            
            // Создаем чек (контракт)
            $contract = ClientContract::create([
                'contract_date' => now(),
                'status' => 1, // 1 - оплачен/завершен
                'contract_number' => $contractNumber,
                'total_amount' => $totalPrice,
                'signed_at' => now(),
                'employee_id' => $doctor->employee_id,
                'client_id' => $appointment->client_id,
                'appointment_id' => $appointment->appointment_id,
            ]);
            
            // Создаем уведомление о завершении приема
            $formattedDate = Carbon::parse($appointment->date)->timezone('Europe/Moscow')->format('d.m.Y H:i');
            $message = "💫 Прием {$formattedDate} завершен. Спасибо за визит! Если вам понравилось, оставьте отзыв.";
            
            Notification::create([
                'client_id' => $appointment->client_id,
                'appointment_id' => $appointment->appointment_id,
                'type' => 'completion',
                'message' => $message,
                'is_read' => false,
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Прием завершен',
                'total_price' => $totalPrice,
                'contract_number' => $contractNumber,
                'materials_used' => $appointment->materials->count(),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Complete appointment error: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка при завершении приема: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Поиск пациентов
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('query');
        
        $patients = Client::where('client_name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get(['client_id', 'client_name', 'phone', 'photo'])
            ->map(function($patient) {
                return [
                    'client_id' => $patient->client_id,
                    'client_name' => $patient->client_name,
                    'phone' => $patient->phone,
                    'photo' => $patient->photo,
                    'photo_url' => $patient->photo ? Storage::url($patient->photo) : null,
                ];
            });
        
        return response()->json($patients);
    }
    
    /**
     * Получить доступные материалы
     */
    public function getAvailableMaterials()
    {
        $materials = Material::where('current_balance', '>', 0)
            ->get(['material_id', 'name', 'unit', 'current_balance']);
        
        return response()->json($materials);
    }
    

    /**
     * Обновить профиль
     */
    public function updateProfile(Request $request)
    {
        $doctor = Auth::guard('employee')->user();
        
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $doctor->employee_id . ',employee_id',
            'employee_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(\+7|8)[0-9]{10}$/',
            ],
        ], [
            'employee_phone.regex' => 'Номер телефона должен быть в формате +7XXXXXXXXXX или 8XXXXXXXXXX (10 цифр после кода)',
            'employee_phone.max' => 'Номер телефона не может быть длиннее 20 символов',
        ]);
        
        DB::table('employees')
            ->where('employee_id', $doctor->employee_id)
            ->update([
                'employee_name' => $request->employee_name,
                'email' => $request->email,
                'employee_phone' => $request->employee_phone,
                'updated_at' => now(),
            ]);
        
        return response()->json(['success' => true]);
    }
}