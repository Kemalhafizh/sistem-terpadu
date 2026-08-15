<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Classroom;

class MigrateFromLegacy extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'data:migrate-legacy {--source= : Direktori atau path file CSV data migrasi}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Migrasi data dari sistem lama SMA ISA 1 ke platform baru';

  /**
   * Execute the console command.
   */
  public function handle(): int
  {
    $source = $this->option('source');

    if (!$source) {
      $this->error('Parameter --source=<path> wajib ditentukan!');
      return Command::FAILURE;
    }

    if (!file_exists($source)) {
      $this->error("Path source tidak ditemukan: {$source}");
      return Command::FAILURE;
    }

    // Determine if source is directory or specific file
    $isDir = is_dir($source);
    
    $studentFile = null;
    $employeeFile = null;

    if ($isDir) {
      $studentFile = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'students.csv';
      $employeeFile = rtrim($source, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'employees.csv';
    } else {
      $filename = basename($source);
      if (str_contains($filename, 'students')) {
        $studentFile = $source;
      } elseif (str_contains($filename, 'employees')) {
        $employeeFile = $source;
      } else {
        $this->error("File tidak dikenali sebagai 'students' atau 'employees' CSV: {$filename}");
        return Command::FAILURE;
      }
    }

    // Process Students
    if ($studentFile && file_exists($studentFile)) {
      $this->info("=== Memulai Impor Data Murid dari: {$studentFile} ===");
      if (!$this->importStudents($studentFile)) {
        return Command::FAILURE;
      }
    }

    // Process Employees
    if ($employeeFile && file_exists($employeeFile)) {
      $this->info("=== Memulai Impor Data Pegawai dari: {$employeeFile} ===");
      if (!$this->importEmployees($employeeFile)) {
        return Command::FAILURE;
      }
    }

    return Command::SUCCESS;
  }

  /**
   * Import Students from CSV.
   */
  private function importStudents(string $filePath): bool
  {
    $rows = $this->parseCsv($filePath);
    if (empty($rows)) {
      $this->error('File CSV kosong atau tidak valid.');
      return false;
    }

    $this->info("Membaca " . count($rows) . " baris data murid...");
    $errors = [];
    $validatedData = [];
    
    // Track duplicates inside the file
    $emailsInCsv = [];
    $nisInCsv = [];
    $nisnInCsv = [];

    foreach ($rows as $index => $row) {
      $lineNum = $index + 2; // 1-based, plus header row

      // Required fields check
      $required = ['name', 'email', 'nis', 'nisn', 'classroom_name', 'parent_name', 'parent_email'];
      foreach ($required as $field) {
        if (empty($row[$field])) {
          $errors[] = "Baris {$lineNum}: Field '{$field}' wajib diisi.";
        }
      }

      if (!empty($errors)) continue;

      // Formats Check
      if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Baris {$lineNum}: Format email murid tidak valid: '{$row['email']}'";
      }
      if (!filter_var($row['parent_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Baris {$lineNum}: Format email orang tua tidak valid: '{$row['parent_email']}'";
      }

      // Duplicate email/nis/nisn check within CSV
      if (in_array($row['email'], $emailsInCsv)) {
        $errors[] = "Baris {$lineNum}: Email murid duplikat di dalam file: '{$row['email']}'";
      }
      $emailsInCsv[] = $row['email'];

      if (in_array($row['nis'], $nisInCsv)) {
        $errors[] = "Baris {$lineNum}: NIS murid duplikat di dalam file: '{$row['nis']}'";
      }
      $nisInCsv[] = $row['nis'];

      if (in_array($row['nisn'], $nisnInCsv)) {
        $errors[] = "Baris {$lineNum}: NISN murid duplikat di dalam file: '{$row['nisn']}'";
      }
      $nisnInCsv[] = $row['nisn'];

      // Duplicate checks against database
      if (User::where('email', $row['email'])->exists()) {
        $errors[] = "Baris {$lineNum}: Email murid '{$row['email']}' sudah terdaftar di database.";
      }
      if (Student::where('nis', $row['nis'])->exists()) {
        $errors[] = "Baris {$lineNum}: NIS '{$row['nis']}' sudah terdaftar di database.";
      }
      if (Student::where('nisn', $row['nisn'])->exists()) {
        $errors[] = "Baris {$lineNum}: NISN '{$row['nisn']}' sudah terdaftar di database.";
      }

      // Classroom lookup (Strict check)
      $classroom = Classroom::where('name', $row['classroom_name'])->first();
      if (!$classroom) {
        $errors[] = "Baris {$lineNum}: Kelas '{$row['classroom_name']}' tidak ditemukan di database. Tolong buat kelas terlebih dahulu.";
      }

      if (empty($errors)) {
        $validatedData[] = [
          'student_name' => $row['name'],
          'student_email' => $row['email'],
          'nis' => $row['nis'],
          'nisn' => $row['nisn'],
          'gender' => $row['gender'] ?? 'L',
          'birth_date' => $row['birth_date'] ?? null,
          'address' => $row['address'] ?? null,
          'classroom_id' => $classroom->id ?? null,
          'parent_name' => $row['parent_name'],
          'parent_email' => $row['parent_email'],
          'parent_phone' => $row['parent_phone'] ?? null,
        ];
      }
    }

    if (!empty($errors)) {
      $this->error('Gagal validasi data murid:');
      foreach (array_slice($errors, 0, 10) as $err) {
        $this->line("- {$err}");
      }
      if (count($errors) > 10) {
        $this->warn("... dan " . (count($errors) - 10) . " eror lainnya.");
      }
      return false;
    }

    $this->info("Validasi sukses! Menyiapkan impor " . count($validatedData) . " data murid...");
    if (!$this->confirm('Apakah Anda yakin ingin memproses impor data murid ini?')) {
      $this->warn('Proses dibatalkan.');
      return false;
    }

    try {
      DB::beginTransaction();

      foreach ($validatedData as $data) {
        // 1. Create or Find Parent User
        $parentUser = User::where('email', $data['parent_email'])->first();
        if (!$parentUser) {
          $parentUser = User::create([
            'name' => $data['parent_name'],
            'email' => $data['parent_email'],
            'phone_number' => $data['parent_phone'],
            'password' => Hash::make('SistemTerpadu123'),
            'role' => 'orang_tua',
            'must_change_password' => true
          ]);
        }

        // 2. Create Student User
        $studentUser = User::create([
          'name' => $data['student_name'],
          'email' => $data['student_email'],
          'password' => Hash::make('SistemTerpadu123'),
          'role' => 'siswa',
          'must_change_password' => true
        ]);

        // 3. Create Student record
        Student::create([
          'user_id' => $studentUser->id,
          'parent_id' => $parentUser->id,
          'classroom_id' => $data['classroom_id'],
          'nis' => $data['nis'],
          'nisn' => $data['nisn'],
          'gender' => $data['gender'],
          'birth_date' => $data['birth_date'],
          'address' => $data['address']
        ]);
      }

      DB::commit();
      $this->info(" Berhasil mengimpor " . count($validatedData) . " murid.");
      return true;
    } catch (\Exception $e) {
      DB::rollBack();
      $this->error("Terjadi kegagalan database saat melakukan impor, data di-rollback: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Import Employees from CSV.
   */
  private function importEmployees(string $filePath): bool
  {
    $rows = $this->parseCsv($filePath);
    if (empty($rows)) {
      $this->error('File CSV kosong atau tidak valid.');
      return false;
    }

    $this->info("Membaca " . count($rows) . " baris data pegawai...");
    $errors = [];
    $validatedData = [];

    $emailsInCsv = [];
    $employeeNumbersInCsv = [];

    foreach ($rows as $index => $row) {
      $lineNum = $index + 2;

      $required = ['name', 'email', 'employee_number', 'position'];
      foreach ($required as $field) {
        if (empty($row[$field])) {
          $errors[] = "Baris {$lineNum}: Field '{$field}' wajib diisi.";
        }
      }

      if (!empty($errors)) continue;

      // Formats Check
      if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Baris {$lineNum}: Format email pegawai tidak valid: '{$row['email']}'";
      }

      // CSV Duplicates
      if (in_array($row['email'], $emailsInCsv)) {
        $errors[] = "Baris {$lineNum}: Email pegawai duplikat di dalam file: '{$row['email']}'";
      }
      $emailsInCsv[] = $row['email'];

      if (in_array($row['employee_number'], $employeeNumbersInCsv)) {
        $errors[] = "Baris {$lineNum}: Nomor Pegawai duplikat di dalam file: '{$row['employee_number']}'";
      }
      $employeeNumbersInCsv[] = $row['employee_number'];

      // DB Duplicates
      if (User::where('email', $row['email'])->exists()) {
        $errors[] = "Baris {$lineNum}: Email pegawai '{$row['email']}' sudah terdaftar di database.";
      }
      if (Employee::where('employee_number', $row['employee_number'])->exists()) {
        $errors[] = "Baris {$lineNum}: Nomor Pegawai '{$row['employee_number']}' sudah terdaftar.";
      }

      // Employment Status validation
      $status = $row['employment_status'] ?? 'gtt';
      if (!in_array($status, ['gty', 'gtt', 'staf_tetap', 'honorer'])) {
        $errors[] = "Baris {$lineNum}: Status kepegawaian tidak valid: '{$status}'. Pilih di antara: gty, gtt, staf_tetap, honorer.";
      }

      if (empty($errors)) {
        $validatedData[] = [
          'name' => $row['name'],
          'email' => $row['email'],
          'employee_number' => $row['employee_number'],
          'position' => $row['position'],
          'employment_status' => $status,
          'base_salary' => $row['base_salary'] ?? 0,
          'hourly_rate' => $row['hourly_rate'] ?? 0,
        ];
      }
    }

    if (!empty($errors)) {
      $this->error('Gagal validasi data pegawai:');
      foreach (array_slice($errors, 0, 10) as $err) {
        $this->line("- {$err}");
      }
      return false;
    }

    $this->info("Validasi sukses! Menyiapkan impor " . count($validatedData) . " data pegawai...");
    if (!$this->confirm('Apakah Anda yakin ingin memproses impor data pegawai ini?')) {
      $this->warn('Proses dibatalkan.');
      return false;
    }

    try {
      DB::beginTransaction();

      foreach ($validatedData as $data) {
        // Determine appropriate role based on position or status
        $role = 'guru';
        $pos = strtolower($data['position']);
        if (str_contains($pos, 'admin') || str_contains($pos, 'super')) {
          $role = 'admin';
        } elseif (str_contains($pos, 'tata usaha') || str_contains($pos, 'tu') || str_contains($pos, 'staf')) {
          $role = 'staf_tu';
        } elseif (str_contains($pos, 'bendahara') || str_contains($pos, 'keuangan')) {
          $role = 'bendahara';
        }

        // 1. Create User
        $user = User::create([
          'name' => $data['name'],
          'email' => $data['email'],
          'password' => Hash::make('SistemTerpadu123'),
          'role' => $role,
          'must_change_password' => true
        ]);

        // 2. Create Employee
        Employee::create([
          'user_id' => $user->id,
          'employee_number' => $data['employee_number'],
          'position' => $data['position'],
          'employment_status' => $data['employment_status'],
          'base_salary' => $data['base_salary'],
          'hourly_rate' => $data['hourly_rate'],
        ]);
      }

      DB::commit();
      $this->info(" Berhasil mengimpor " . count($validatedData) . " pegawai.");
      return true;
    } catch (\Exception $e) {
      DB::rollBack();
      $this->error("Terjadi kegagalan database saat melakukan impor, data di-rollback: " . $e->getMessage());
      return false;
    }
  }

  /**
   * Parse CSV file with header mapping.
   */
  private function parseCsv(string $filePath): array
  {
    $handle = fopen($filePath, 'r');
    if (!$handle) return [];

    $header = fgetcsv($handle, 1000, ',');
    if (!$header) {
      fclose($handle);
      return [];
    }

    // Clean headers
    $header = array_map(function ($h) {
      return trim(strtolower($h));
    }, $header);

    $rows = [];
    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
      // Skip empty rows
      if (count($data) == 1 && empty($data[0])) continue;
      
      $row = [];
      foreach ($header as $index => $colName) {
        $row[$colName] = $data[$index] ?? null;
      }
      $rows[] = $row;
    }

    fclose($handle);
    return $rows;
  }
}
