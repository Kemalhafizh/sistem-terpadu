<?php

use App\Models\Attendance;
use App\Models\Billing;
use App\Models\EmployeeDocument;
use App\Models\EmployeeTraining;
use App\Models\Extracurricular;
use App\Models\ExtracurricularMember;
use App\Models\Grade;
use App\Models\HealthScreening;
use App\Models\HealthScreeningResult;
use App\Models\OsisCandidate;
use App\Models\OsisElection;
use App\Models\OsisMember;
use App\Models\OsisVote;
use App\Models\OsisWorkProgram;
use App\Models\PerformanceEvaluation;
use App\Models\Registration;
use App\Models\SpmbPeriod;
use App\Models\SpmbTrack;
use App\Models\SpmbWave;
use App\Models\StudentHealthRecord;
use App\Models\UksVisit;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappBroadcast;
use App\Models\WhatsappLog;
use App\Models\WhatsappTemplate;

test('mega seeder v2 seeds all tables correctly and successfully', function () {
    // 1. Run seeders
    $this->artisan('db:seed');

    // 2. Assert SPMB (PPDB) counts
    expect(SpmbPeriod::count())->toBeGreaterThanOrEqual(1);
    expect(SpmbWave::count())->toBeGreaterThanOrEqual(2);
    expect(SpmbTrack::count())->toBeGreaterThanOrEqual(3);
    expect(Registration::count())->toBeGreaterThanOrEqual(70);

    // 3. Assert HRD counts
    expect(PerformanceEvaluation::count())->toBeGreaterThanOrEqual(10);
    expect(EmployeeTraining::count())->toBeGreaterThanOrEqual(30);
    expect(EmployeeDocument::count())->toBeGreaterThanOrEqual(50);

    // 4. Assert Extracurricular counts
    expect(Extracurricular::count())->toBeGreaterThanOrEqual(7);
    expect(ExtracurricularMember::count())->toBeGreaterThanOrEqual(50);

    // 5. Assert OSIS & MPK counts
    expect(OsisMember::count())->toBeGreaterThanOrEqual(12);
    expect(OsisWorkProgram::count())->toBeGreaterThanOrEqual(8);
    expect(OsisElection::count())->toBeGreaterThanOrEqual(1);
    expect(OsisCandidate::count())->toBeGreaterThanOrEqual(3);
    expect(OsisVote::count())->toBeGreaterThanOrEqual(100);

    // 6. Assert Health & UKS counts
    expect(StudentHealthRecord::count())->toBeGreaterThanOrEqual(60);
    expect(HealthScreening::count())->toBeGreaterThanOrEqual(1);
    expect(HealthScreeningResult::count())->toBeGreaterThanOrEqual(60);
    expect(UksVisit::count())->toBeGreaterThanOrEqual(30);

    // 7. Assert WhatsApp Gateway counts
    expect(WhatsappTemplate::count())->toBeGreaterThanOrEqual(5);
    expect(WhatsappAutomation::count())->toBeGreaterThanOrEqual(3);
    expect(WhatsappBroadcast::count())->toBeGreaterThanOrEqual(3);
    expect(WhatsappLog::count())->toBeGreaterThanOrEqual(15);

    // 8. Assert Analytics past semester counts
    // Attendance, Grade, and Billing should have seeded historical data
    expect(Attendance::count())->toBeGreaterThanOrEqual(100);
    expect(Grade::count())->toBeGreaterThanOrEqual(100);
    expect(Billing::count())->toBeGreaterThanOrEqual(100);
});
