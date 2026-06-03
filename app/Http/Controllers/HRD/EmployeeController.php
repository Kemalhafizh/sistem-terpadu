<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with('user');

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('employee_number', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('employment_status', $request->status);
        }

        $employees = $query->latest()->paginate(10);

        return view('hrd.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all users who have staff/teacher roles but don't have an employee record yet
        $availableUsers = User::whereIn('role', [
            'guru', 'staf_tu', 'admin', 'superadmin', 'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas', 'bk',
        ])->doesntHave('employee')->get();

        return view('hrd.employees.create', compact('availableUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:employees,user_id',
            'employee_number' => 'required|string|unique:employees,employee_number',
            'position' => 'required|string|max:255',
            'employment_status' => 'required|in:gty,gtt,staf_tetap,honorer',
            'hire_date' => 'required|date',
            'base_salary' => 'nullable|numeric',
            'hourly_rate' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
        ]);

        Employee::create($validated);

        return redirect()->route('hrd.employees.index')->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $employee->load(['user', 'attendances' => function ($q) {
            $q->latest('date')->take(10);
        }, 'leaveRequests' => function ($q) {
            $q->latest()->take(5);
        }]);

        return view('hrd.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('hrd.employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_number' => 'required|string|unique:employees,employee_number,'.$employee->id,
            'position' => 'required|string|max:255',
            'employment_status' => 'required|in:gty,gtt,staf_tetap,honorer',
            'hire_date' => 'required|date',
            'base_salary' => 'nullable|numeric',
            'hourly_rate' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
        ]);

        $employee->update($validated);

        return redirect()->route('hrd.employees.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('hrd.employees.index')->with('success', 'Data pegawai berhasil dihapus.');
    }
}
