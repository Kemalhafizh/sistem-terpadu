<?php

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create([
        'name' => 'HR Admin',
        'email' => 'hradmin@sista.com',
        'role' => 'admin',
    ]);

    $this->teacherUser = User::factory()->create([
        'name' => 'Teacher One',
        'email' => 'teacherone@sista.com',
        'role' => 'guru',
    ]);

    $this->employee = Employee::create([
        'user_id' => $this->teacherUser->id,
        'employee_number' => 'NIY.202611001',
        'position' => 'Guru Agama',
        'employment_status' => 'gty',
    ]);
});

test('admin can access documents page for employee', function () {
    $response = $this->actingAs($this->admin)->get(route('hrd.employees.documents', $this->employee));
    $response->assertStatus(200);
});

test('admin can upload employee document successfully', function () {
    Storage::fake('local');

    $file = UploadedFile::fake()->create('contract.pdf', 500);

    $response = $this->actingAs($this->admin)->post(route('hrd.employees.documents.store', $this->employee), [
        'title' => 'Kontrak Kerja 2026',
        'type' => 'kontrak',
        'file' => $file,
        'issue_date' => '2026-01-01',
        'expiry_date' => '2027-01-01',
        'notes' => 'Catatan kontrak',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('employee_documents', [
        'employee_id' => $this->employee->id,
        'title' => 'Kontrak Kerja 2026',
        'type' => 'kontrak',
    ]);
});

test('admin can delete employee document', function () {
    $doc = EmployeeDocument::create([
        'employee_id' => $this->employee->id,
        'title' => 'Ijazah Pegawai',
        'type' => 'ijazah',
        'file_path' => 'documents/ijazah.pdf',
    ]);

    $response = $this->actingAs($this->admin)->delete(route('hrd.employees.documents.destroy', [$this->employee, $doc]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('employee_documents', [
        'id' => $doc->id,
    ]);
});
