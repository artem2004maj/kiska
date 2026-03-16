<?php
// app/Http/Controllers/WelcomeController.php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Service;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index()
    {
        // Получаем врачей (сотрудников с ролью doctor)
        $doctors = Employee::where('role', 'doctor')
            ->select('employee_id', 'employee_name', 'role')
            ->get();
        
        // Получаем услуги
        $services = Service::where('is_active', 1)
            ->select('service_id', 'service_name', 'service_category', 'default_price')
            ->orderBy('service_category')
            ->get();
        
        // Получаем последние отзывы - ИСПРАВЛЕННЫЙ ЗАПРОС
        $testimonials = Feedback::with('client')
            ->whereHas('appointment', function($q) {
                $q->where('status', 2); // Только завершенные приемы
            })
            ->latest()
            ->take(5)
            ->get()
            ->map(function($feedback) {
                // Получаем название услуги через appointment -> providedServices -> service
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