<?php

namespace App\Services;

use App\Mail\StudentAccountCreated;
use App\Models\Billing;
use App\Models\PaymentCategory;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Class RegistrationService
 *
 * Menangani alur penerimaan siswa baru (SPMB), termasuk konversi calon siswa
 * yang diterima menjadi pengguna sistem dan siswa aktif, serta pen-generate-an
 * tagihan awal.
 */
class RegistrationService
{
    /**
     * Convert an accepted registration to a student user and generate initial billing.
     *
     * Proses ini dijalankan dalam satu transaksi database:
     * 1. Membuat akun User dengan role 'student'.
     * 2. Membuat profil Student terkait.
     * 3. Mengaitkan Registration dengan User ID baru.
     * 4. Menerbitkan tagihan Uang Pangkal pertama untuk siswa tersebut.
     * 5. Mengirimkan email berisi kredensial ke email pendaftar.
     *
     * @param  Registration  $registration  Data pendaftaran yang telah berstatus diterima
     * @return array Mengembalikan array berisi 'message', 'password' (sementara), dan 'email'
     *
     * @throws \Exception Jika pendaftar sudah pernah dikonversi
     */
    public function convertToStudent(Registration $registration): array
    {
        if ($registration->user_id != null) {
            throw new \Exception('Pendaftar ini sudah dikonversi sebelumnya.');
        }

        return DB::transaction(function () use ($registration) {
            // 1. Create User Account
            $email = strtolower($registration->registration_number).'@student.sch.id';
            $password = Str::random(10);

            $newUser = User::create([
                'name' => $registration->full_name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'student',
                'must_change_password' => true,
            ]);

            // 2. Create Student Profile
            $nis = 'NIS-'.date('Y').'-'.substr($registration->registration_number, -4);
            $nisn = $registration->nisn ?: 'NSN'.time();

            $newStudent = Student::create([
                'user_id' => $newUser->id,
                'classroom_id' => null,
                'nis' => $nis,
                'nisn' => $nisn,
                'gender' => 'L',
                'address' => 'Belum diisi',
            ]);

            // 3. Link User ID to Registration
            $registration->user_id = $newUser->id;
            $registration->status = 'accepted';
            $registration->save();

            // 4. Generate Initial Billing (Uang Pangkal)
            $paymentCategory = PaymentCategory::firstOrCreate(
                ['name' => 'Uang Pangkal / Pendaftaran'],
                ['default_amount' => 5000000, 'is_monthly' => false]
            );

            Billing::create([
                'student_id' => $newStudent->id,
                'payment_category_id' => $paymentCategory->id,
                'academic_year_id' => $registration->academic_year_id,
                'month' => null,
                'amount' => $paymentCategory->default_amount,
                'due_date' => now()->addDays(14)->format('Y-m-d'),
                'status' => 'unpaid',
            ]);

            // Send Account Email
            try {
                Mail::to($registration->email)->send(new StudentAccountCreated($newUser, $password));
            } catch (\Exception $e) {
                Log::error('Failed to send student account email: '.$e->getMessage());
            }

            $message = "Berhasil! Pendaftar Diterima. Akun siswa otomatis dibuat dengan Email: {$email}, Password Sementara: {$password}. Siswa wajib ganti password saat login pertama.";

            return [
                'message' => $message,
                'password' => $password,
                'email' => $email,
            ];
        });
    }
}
