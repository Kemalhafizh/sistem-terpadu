<?php

namespace App\Services;

use App\Mail\StudentAccountCreated;
use App\Models\Billing;
use App\Models\PaymentCategory;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use App\Services\Auth\AccountProvisioningService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
     * 2. Membuat profil Student terkait dan menautkan akun orang tua bersama berdasarkan no. HP.
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

        // A student needs their real NISN (Dapodik, the app). An empty one
        // used to become an invented "NSN<timestamp>".
        if (blank($registration->nisn)) {
            throw new \DomainException(__('NISN :name belum diisi. Lengkapi NISN pendaftar sebelum dijadikan siswa.', ['name' => $registration->full_name]));
        }

        return DB::transaction(function () use ($registration) {
            $provisioning = app(AccountProvisioningService::class);

            // 1. Create User Account
            $email = strtolower($registration->registration_number).'@student.sch.id';
            $password = $provisioning->generateTemporaryPassword(10);

            $newUser = User::create([
                'name' => $registration->full_name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'student',
                'account_type' => 'siswa',
                'must_change_password' => true,
            ]);

            // 2. Create Student Profile
            $nis = 'NIS-'.date('Y').'-'.substr($registration->registration_number, -4);

            // What the applicant filled in on the SPMB form. This used to be
            // gender "L" for everyone, address "Belum diisi", and no birth date.
            $newStudent = Student::create([
                'user_id' => $newUser->id,
                'classroom_id' => null,
                'nis' => $nis,
                'nisn' => $registration->nisn,
                'gender' => in_array($registration->gender, ['L', 'P'], true) ? $registration->gender : 'L',
                'birth_place' => $registration->birth_place,
                'birth_date' => $registration->birth_date,
                'address' => $registration->address,
            ]);

            // 2b. K5: Link or create one shared parent account keyed by parent_phone.
            $parentAccount = $provisioning->ensureParentForStudent(
                $newStudent,
                $registration->parent_name,
                $registration->parent_phone
            );

            // 3. Link User ID to Registration
            $registration->user_id = $newUser->id;
            $registration->status = 'accepted';
            $registration->save();

            // 4. Initial billing (Uang Pangkal), at the amount Keuangan set.
            // There used to be a made-up Rp 5.000.000 category when the
            // school had none (and it never matched the seeded "Uang Pangkal").
            $paymentCategory = PaymentCategory::where('is_monthly', false)
                ->where('name', 'like', 'Uang Pangkal%')
                ->where('default_amount', '>', 0)
                ->orderBy('id')
                ->first();

            if ($paymentCategory) {
                Billing::create([
                    'student_id' => $newStudent->id,
                    'payment_category_id' => $paymentCategory->id,
                    'academic_year_id' => $registration->academic_year_id,
                    'month' => null,
                    'amount' => $paymentCategory->default_amount,
                    'due_date' => now()->addDays(14)->format('Y-m-d'),
                    'status' => 'unpaid',
                ]);
            }

            // Send Account Email
            try {
                Mail::to($registration->email)->send(new StudentAccountCreated($newUser, $password));
            } catch (\Exception $e) {
                Log::error('Failed to send student account email: '.$e->getMessage());
            }

            $message = "Berhasil! Pendaftar Diterima. Akun siswa otomatis dibuat dengan Email/NISN: {$email} ({$registration->nisn}). Siswa wajib ganti password saat login pertama.";
            if (! $paymentCategory) {
                $message .= ' '.__('Tagihan uang pangkal belum dibuat karena kategori "Uang Pangkal" beserta nominalnya belum diatur di Keuangan.');
            }

            return [
                'message' => $message,
                'password' => $password,
                'email' => $email,
                'parent_created' => $parentAccount['created'] ?? false,
                'parent_password' => $parentAccount['plain_password'] ?? null,
                'billed' => (bool) $paymentCategory,
            ];
        });
    }
}
