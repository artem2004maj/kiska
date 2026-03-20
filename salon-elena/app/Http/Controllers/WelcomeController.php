<?php
// app/Http/Controllers/WelcomeController.php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Service;
use App\Models\Feedback;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class WelcomeController extends Controller
{
    public function index()
    {
        // Получаем врачей с фото и расписанием
        $doctors = Employee::where('role', 'doctor')
            ->with('schedules')
            ->select('employee_id', 'employee_name', 'role', 'photo')
            ->get()
            ->map(function ($doctor) {
                // Формируем расписание на неделю
                $schedule = [];
                $days = [1, 2, 3, 4, 5, 6, 0]; // Пн, Вт, Ср, Чт, Пт, Сб, Вс
                
                foreach ($days as $day) {
                    $daySchedule = $doctor->getScheduleForDay($day);
                    if ($daySchedule && $daySchedule->start_time && $daySchedule->end_time) {
                        $schedule[$day] = [
                            'start' => Carbon::parse($daySchedule->start_time)->format('H:i'),
                            'end' => Carbon::parse($daySchedule->end_time)->format('H:i'),
                            'working' => true
                        ];
                    } else {
                        $schedule[$day] = ['working' => false];
                    }
                }
                
                return [
                    'employee_id' => $doctor->employee_id,
                    'employee_name' => $doctor->employee_name,
                    'role' => $doctor->role,
                    'photo_url' => $doctor->photo ? Storage::url($doctor->photo) : null,
                    'schedule' => $schedule,
                ];
            });
        
        $services = Service::where('is_active', 1)
            ->select('service_id', 'service_name', 'service_category', 'default_price')
            ->orderBy('service_category')
            ->get();
        
        $testimonials = Feedback::with('client')
            ->whereHas('appointment', function($q) {
                $q->where('status', 2);
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function($feedback) {
                $serviceName = null;
                if ($feedback->appointment && $feedback->appointment->providedServices) {
                    $firstService = $feedback->appointment->providedServices->first();
                    if ($firstService && $firstService->service) {
                        $serviceName = $firstService->service->service_name;
                    }
                }
                
                return [
                    'feedback_id' => $feedback->feedback_id,
                    'score' => $feedback->score,
                    'comment' => $feedback->comment,
                    'client' => $feedback->client ? [
                        'client_name' => $feedback->client->client_name
                    ] : ['client_name' => 'Клиент'],
                    'service_name' => $serviceName ?? 'Услуга',
                    'created_at' => $feedback->created_at,
                ];
            });
        
        return Inertia::render('Welcome', [
            'canLogin' => true,
            'canRegister' => true,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
            'doctors' => $doctors,
            'services' => $services,
            'testimonials' => $testimonials,
        ]);
    }
}