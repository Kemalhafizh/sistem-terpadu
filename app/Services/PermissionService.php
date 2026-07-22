<?php

namespace App\Services;

class PermissionService
{
    /**
     * Get all enterprise system roles and their permission matrix.
     */
    public function getRolePermissionMatrix(): array
    {
        return [
            'admin' => [
                'label' => 'Administrator Utama',
                'permissions' => ['academic.*', 'finance.*', 'security.*', 'observability.*', 'hrd.*'],
            ],
            'superadmin' => [
                'label' => 'Super Administrator (YBWSA)',
                'permissions' => ['*'],
            ],
            'guru' => [
                'label' => 'Guru Pengajar',
                'permissions' => ['academic.grades.edit', 'academic.cbt.manage', 'keislaman.tahfidz.input'],
            ],
            'bendahara' => [
                'label' => 'Bendahara / Keuangan',
                'permissions' => ['finance.ledger.view', 'finance.billing.manage', 'finance.wadiah.process'],
            ],
            'staf_tu' => [
                'label' => 'Staf Tata Usaha',
                'permissions' => ['academic.student.manage', 'integration.dapodik.sync'],
            ],
            'kepala_sekolah' => [
                'label' => 'Kepala Sekolah',
                'permissions' => ['academic.reports.approve', 'finance.reports.view', 'hrd.payroll.view'],
            ],
            'student' => [
                'label' => 'Siswa',
                'permissions' => ['academic.grades.view', 'canteen.wadiah.pay'],
            ],
            'wali_murid' => [
                'label' => 'Wali Murid',
                'permissions' => ['academic.student_grades.view', 'finance.billings.pay'],
            ],
        ];
    }

    /**
     * Check if role has specific permission.
     */
    public function hasPermission(string $role, string $permission): bool
    {
        $matrix = $this->getRolePermissionMatrix();
        if (! isset($matrix[$role])) {
            return false;
        }

        $rolePermissions = $matrix[$role]['permissions'];

        if (in_array('*', $rolePermissions)) {
            return true;
        }

        foreach ($rolePermissions as $p) {
            if ($p === $permission) {
                return true;
            }

            // Wildcard matching (e.g. academic.*)
            if (str_contains($p, '.*')) {
                $prefix = str_replace('.*', '', $p);
                if (str_starts_with($permission, $prefix)) {
                    return true;
                }
            }
        }

        return false;
    }
}
