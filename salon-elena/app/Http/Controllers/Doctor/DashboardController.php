<?php
// app/Http/Controllers/Doctor/DashboardController.php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Material;
use App\Models\Service;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            ->get();
        
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
                    'birth_date' => $patient->birth_date,
                    'last_visit' => $lastAppointment ? $lastAppointment->date : null,
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
                    'birth_date' => $patient->birth_date,
                    'last_visit' => $lastAppointment ? $lastAppointment->date : null,
                ];
            });
        
        return Inertia::render('Doctor/Patients', [
            'doctor' => [
                'employee_id' => $doctor->employee_id,
                'employee_name' => $doctor->employee_name,
                'email' => $doctor->email,
                'employee_phone' => $doctor->employee_phone,
                'role' => $doctor->role,
            ],
            'patients' => $patients,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    /**
     * Страница "Мои услуги"
     */
    public function services()
    {
        $doctor = Auth::guard('employee')->user();
        
        $services = Service::where('is_active', 1)
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
        
        $records = MedicalRecord::with('employee')
            ->where('client_id', $clientId)
            ->orderBy('visit_date', 'desc')
            ->get();
        
        $appointments = Appointment::with(['providedServices.service'])
            ->where('client_id', $clientId)
            ->where('employee_id', $doctor->employee_id)
            ->orderBy('date', 'desc')
            ->get();
        
        return Inertia::render('Doctor/MedicalRecord', [
            'doctor' => [
                'employee_id' => $doctor->employee_id,
                'employee_name' => $doctor->employee_name,
                'email' => $doctor->email,
                'employee_phone' => $doctor->employee_phone,
                'role' => $doctor->role,
            ],
            'patient' => $patient,
            'records' => $records,
            'appointments' => $appointments,
            'laravelVersion' => app()->version(),
            'phpVersion' => PHP_VERSION,
        ]);
    }
    
    // API методы (оставляем как есть)
    public function getAppointments(Request $request)
    {
        $doctor = Auth::guard('employee')->user();
        $view = $request->get('view', 'week');
        $date = $request->get('date', now()->toDateString());
        
        $query = Appointment::with(['client', 'providedServices.service', 'materials.material'])
            ->where('employee_id', $doctor->employee_id);
        
        if ($view === 'day') {
            $query->whereDate('date', $date);
        } else {
            $start = now()->parse($date)->startOfWeek();
            $end = now()->parse($date)->endOfWeek();
            $query->whereBetween('date', [$start, $end]);
        }
        
        return response()->json($query->orderBy('date')->get());
    }
    
    public function getAppointment($id)
    {
        $appointment = Appointment::with(['client', 'providedServices.service', 'materials.material'])
            ->findOrFail($id);
        
        return response()->json($appointment);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = $request->status;
        $appointment->save();
        
        return response()->json(['success' => true]);
    }
    
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
                
                if ($material->current_balance < $materialData['quantity_used']) {
                    throw new \Exception("Недостаточно {$material->name} на складе. Доступно: {$material->current_balance} {$material->unit}");
                }

                $material->current_balance -= $materialData['quantity_used'];
                $material->save();

                $appointment->materials()->attach($materialData['material_id'], [
                    'quantity_used' => $materialData['quantity_used'],
                    'cost_price' => 0,
                    'notes' => $materialData['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('consumption')->insert([
                    'batch_number' => 'APT-' . $id . '-' . time(),
                    'quantity' => $materialData['quantity_used'],
                    'cost_price' => 0,
                    'provided_id' => null,
                    'material_id' => $materialData['material_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Материалы успешно сохранены']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
    
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
    
    public function completeAppointment(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->status = 2;
        $appointment->save();
        
        return response()->json(['success' => true]);
    }
    
    public function searchPatients(Request $request)
    {
        $query = $request->get('query');
        
        $patients = Client::where('client_name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(10)
            ->get(['client_id', 'client_name', 'phone']);
        
        return response()->json($patients);
    }
    
    public function getAvailableMaterials()
    {
        $materials = Material::where('current_balance', '>', 0)
            ->get(['material_id', 'name', 'unit', 'current_balance']);
        
        return response()->json($materials);
    }
    
    public function updateProfile(Request $request)
    {
        $doctor = Auth::guard('employee')->user();
        
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $doctor->employee_id . ',employee_id',
            'employee_phone' => 'nullable|string|max:20',
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