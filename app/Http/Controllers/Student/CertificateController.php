<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Middleware\RoleMiddleware;
use App\Models\IssuedCertificate;
use App\Models\Student;
use App\Support\Access\Roles;
use App\Support\Access\StudentDataAccess;

class CertificateController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isStaffReader = $user !== null && RoleMiddleware::allowsUser($user, [
            ...StudentDataAccess::TEACHER_ROLES,
            'wali_kelas',
            'bk',
            'staf_tu',
            ...Roles::LEADERSHIP,
            'admin',
            'superadmin',
        ]);

        $query = IssuedCertificate::with(['student', 'template'])->latest();

        if (! $isStaffReader && $user !== null) {
            if (RoleMiddleware::allowsUser($user, StudentDataAccess::PARENT_ROLES)) {
                $childUserIds = Student::where('parent_id', $user->id)->pluck('user_id');
                $query->whereIn('student_id', $childUserIds);
            } else {
                $query->where('student_id', $user->id);
            }
        }

        $certificates = $query->get();

        return view('certificates.templates', compact('certificates'));
    }

    public function verifyPage(string $code)
    {
        $cert = IssuedCertificate::with(['student', 'template'])->where('verification_code', $code)->firstOrFail();

        return view('certificates.verify', compact('cert'));
    }
}
