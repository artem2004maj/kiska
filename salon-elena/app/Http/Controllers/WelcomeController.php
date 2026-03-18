<?php
// app/Http/Controllers/WelcomeController.php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Service;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage; // ДОБАВЛЕНО

class WelcomeController extends Controller
{
    public function index()
    {
        // Получаем врачей (сотрудников с ролью doctor) с фото
        $doctors = Employee::where('role', 'doctor')
            ->select('employee_id', 'employee_name', 'role', 'photo') // ДОБАВЛЕНО photo
            ->get()
            ->map(function ($doctor) {
                return [
                    'employee_id' => $doctor->employee_id,
                    'employee_name' => $doctor->employee_name,
                    'role' => $doctor->role,
                    'photo_url' => $doctor->photo ? Storage::url($doctor->photo) : null, // ДОБАВЛЕНО
                ];
            });
        
        // Получаем услуги
        $services = Service::where('is_active', 1)
            ->select('service_id', 'service_name', 'service_category', 'default_price')
            ->orderBy('service_category')
            ->get();
        
        // Получаем последние отзывы
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