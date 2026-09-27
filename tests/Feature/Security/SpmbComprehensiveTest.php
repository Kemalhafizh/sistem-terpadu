<?php

namespace Tests\Feature\Security;

use App\Models\AcademicYear;
use App\Models\PaymentCategory;
use App\Models\Registration;
use App\Models\SpmbDocument;
use App\Models\SpmbPeriod;
use App\Models\SpmbTrack;
use App\Models\SpmbWave;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SpmbComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $academicYear;

    protected $period;

    protected $wave;

    protected $track;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create Admin
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create Active Academic Year
        $this->academicYear = AcademicYear::factory()->create([
            'is_active' => true,
            'year' => '2026/2027',
            'semester' => 'Ganjil',
        ]);

        // Create SPMB Period
        $this->period = SpmbPeriod::create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'SPMB 2026/2027',
            'total_quota' => 100,
            'status' => 'open',
        ]);

        // Create SPMB Wave
        $this->wave = SpmbWave::create([
            'spmb_period_id' => $this->period->id,
            'name' => 'Gelombang 1',
            'registration_start' => now()->subDays(5)->format('Y-m-d'),
            'registration_end' => now()->addDays(5)->format('Y-m-d'),
            'announcement_date' => now()->addDays(10)->format('Y-m-d'),
            'quota' => 2, // Low quota for ranking test
            'registration_fee' => 150000,
            'status' => 'open',
        ]);

        // Create SPMB Track
        $this->track = SpmbTrack::create([
            'spmb_period_id' => $this->period->id,
            'name' => 'Reguler',
            'quota' => 2,
            'scoring_weights' => [
                'rapor' => 40,
                'tes' => 30,
                'prestasi' => 15,
                'tahfidz' => 15,
            ],
            'requirements' => 'Persyaratan Reguler',
        ]);
    }

    /**
     * Test public applicant SPMB registration wizard submission.
     */
    public function test_public_applicant_can_submit_registration()
    {
        $response = $this->get(route('spmb.create'));
        $response->assertStatus(200);

        // Submit form
        $submitData = [
            'spmb_wave_id' => $this->wave->id,
            'spmb_track_id' => $this->track->id,
            'full_name' => 'Aditya Wijaya',
            'email' => 'aditya@example.com',
            'nisn' => '3012345678',
            'previous_school' => 'SMP N 1 Semarang',
            'parent_phone' => '081234567890',
            'gender' => 'L',
            'birth_date' => '2010-05-15',
            'birth_place' => 'Semarang',
            'address' => 'Jl. Pemuda No. 100, Semarang',
            'parent_name' => 'Budi Wijaya',
            'document_kk' => UploadedFile::fake()->create('kk.pdf', 500, 'application/pdf'),
            'document_certificate' => UploadedFile::fake()->create('ijazah.pdf', 500, 'application/pdf'),
            'document_skhun' => UploadedFile::fake()->create('skhun.pdf', 500, 'application/pdf'),
            'document_akta' => UploadedFile::fake()->create('akta.pdf', 500, 'application/pdf'),
            'photo' => UploadedFile::fake()->image('photo.jpg'),
            'rapor_scores' => [
                'matematika' => 85,
                'ipa' => 80,
                'ips' => 80,
                'indonesia' => 90,
                'inggris' => 85,
            ],
            'tahfidz_data' => [
                'juz_count' => 2,
            ],
            'achievement_data' => [
                [
                    'title' => 'Juara 1 MTQ',
                    'level' => 'provinsi',
                ],
            ],
        ];

        $response = $this->post(route('spmb.store'), $submitData);

        // Should redirect to success
        $response->assertRedirect(route('spmb.success'));
        $response->assertSessionHas('registration_number');

        // Check DB
        $registration = Registration::where('email', 'aditya@example.com')->first();
        $this->assertNotNull($registration);
        $this->assertEquals('Aditya Wijaya', $registration->full_name);
        $this->assertEquals(5, $registration->spmbDocuments()->count());
    }

    /**
     * Test admin SPMB actions: periods, waves, tracks, verification, scoring, selection, conversion.
     */
    public function test_admin_spmb_full_management_workflow()
    {
        // 1. Create registrations to verify & score
        $applicant1 = Registration::create([
            'registration_number' => 'SPMB-2026-0001',
            'spmb_wave_id' => $this->wave->id,
            'spmb_track_id' => $this->track->id,
            'full_name' => 'Siswa Satu',
            'email' => 'siswa1@example.com',
            'nisn' => '3012345601',
            'previous_school' => 'SMP 1',
            'parent_phone' => '081200000001',
            'gender' => 'L',
            'birth_date' => '2010-01-01',
            'birth_place' => 'Semarang',
            'address' => 'Semarang',
            'parent_name' => 'Wali Satu',
            'rapor_scores' => ['matematika' => 80, 'ipa' => 80, 'ips' => 80, 'indonesia' => 80, 'inggris' => 80],
            'tahfidz_data' => ['juz_count' => 0],
            'status' => 'pending',
            'document_status' => 'submitted',
            'academic_year_id' => $this->academicYear->id,
        ]);

        $applicant2 = Registration::create([
            'registration_number' => 'SPMB-2026-0002',
            'spmb_wave_id' => $this->wave->id,
            'spmb_track_id' => $this->track->id,
            'full_name' => 'Siswa Dua',
            'email' => 'siswa2@example.com',
            'nisn' => '3012345602',
            'previous_school' => 'SMP 2',
            'parent_phone' => '081200000002',
            'gender' => 'P',
            'birth_date' => '2010-02-02',
            'birth_place' => 'Semarang',
            'address' => 'Semarang',
            'parent_name' => 'Wali Dua',
            'rapor_scores' => ['matematika' => 90, 'ipa' => 95, 'ips' => 90, 'indonesia' => 95, 'inggris' => 90],
            'tahfidz_data' => ['juz_count' => 2],
            'status' => 'pending',
            'document_status' => 'submitted',
            'academic_year_id' => $this->academicYear->id,
        ]);

        $doc = SpmbDocument::create([
            'registration_id' => $applicant1->id,
            'document_type' => 'kk',
            'file_path' => 'spmb/kk/dummy.pdf',
        ]);

        // 2. Admin Document Verification
        $response = $this->actingAs($this->admin)
            ->post(route('spmb.admin.documents.verify', $doc->id), [
                'verification_status' => 'verified',
            ]);
        $response->assertStatus(302); // Redirect back
        $this->assertEquals('verified', $doc->fresh()->verification_status);

        // 3. Admin Entrance Test Score Entry
        $response = $this->actingAs($this->admin)
            ->post(route('spmb.admin.scoring.store', $this->wave), [
                'scores' => [
                    $applicant1->id => 75,
                    $applicant2->id => 90,
                ],
            ]);
        $response->assertRedirect(route('spmb.admin.scoring', $this->wave));
        $this->assertEquals(75, $applicant1->fresh()->entrance_test_score);
        $this->assertEquals(90, $applicant2->fresh()->entrance_test_score);

        // 4. Admin Run Quota-Based Selection
        $response = $this->actingAs($this->admin)
            ->post(route('spmb.admin.selection.run', $this->wave));
        $response->assertRedirect(route('spmb.admin.selection', $this->wave));

        // Applicant 2 has higher score and should rank #1 and be accepted
        $applicant1 = $applicant1->fresh();
        $applicant2 = $applicant2->fresh();

        $this->assertEquals(1, $applicant2->rank);
        $this->assertEquals('accepted', $applicant2->status);

        $this->assertEquals(2, $applicant1->rank);
        $this->assertEquals('accepted', $applicant1->status); // Quota was set to 2, so both should be accepted!

        // 5. Admin Confirm Re-registration Payment
        $response = $this->actingAs($this->admin)
            ->post(route('spmb.admin.reregistration.confirm', $applicant2));
        $response->assertStatus(302);
        $this->assertTrue($applicant2->fresh()->has_reregistered);

        // 6. Admin Convert Re-registered Applicant to Student
        // Need to create default payment category first as RegistrationService queries it
        PaymentCategory::create([
            'name' => 'Uang Pangkal / Pendaftaran',
            'default_amount' => 5000000,
            'is_monthly' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('spmb.admin.bulk-convert', $this->wave));
        $response->assertStatus(302);

        // Applicant 2 is converted, user account created
        $applicant2 = $applicant2->fresh();
        $this->assertNotNull($applicant2->user_id);

        $student = Student::where('user_id', $applicant2->user_id)->first();
        $this->assertNotNull($student);
    }
}
