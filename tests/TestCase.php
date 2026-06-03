<?php

namespace Tests;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\PaymentCategory;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradeService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property User $guru
 * @property User $admin
 * @property User $superadmin
 * @property User $kepsek
 * @property User $wakaSarpras
 * @property User $bk
 * @property User $studentUser
 * @property User $parent
 * @property Student $student
 * @property Student $student1
 * @property Student $student2
 * @property AcademicYear $academicYear
 * @property Classroom $classroom
 * @property Subject $subject
 * @property PaymentCategory $category
 * @property GradeService $service
 */
abstract class TestCase extends BaseTestCase
{
    //
}
