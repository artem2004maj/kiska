<?php
// app/Http/Controllers/Client/DashboardController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\MedicalRecord;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();
        
        $appointments = Appointment::with(['employee', 'providedServices.service'])
            ->where('client_id', $client->client_id)
            ->orderBy('date', 'desc')
            ->get();
        
        $services = Service::where('is_active', 1)->get();
        
        $medicalRecords = MedicalRecord::with('employee')
            ->where('client_id', $client->client_id)
            ->orderBy('visit_date', 'desc')
            ->get();
        
        $feedback = Feedback::where('client_id', $client->client_id)->get();
        
        return Inertia::render('Dashboard/Client', [
            'client' => $client,
            'appointments' => $appointments,
            'services' => $services,
            'medicalRecords' => $medicalRecords,
            'feedback' => $feedback,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
}