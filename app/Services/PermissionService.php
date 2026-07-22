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
            'superadmin' => [
                'label' => 'Super Administrator (Ultimate Single Account YBWSA)',
                'description' => 'Akun tunggal pemilik hak akses penuh tanpa batas (*), pengontrol utama seluruh modul dan kebijakan enterprise.',
                'permissions' => ['*'],
            ],
            'admin' => [
                'label' => 'Administrator Operasional & Observabilitas (Troubleshoot & Monitor)',
                'description' => 'Administrator teknis terbatas untuk pemantauan sistem, troubleshooting, monitoring APM, queue, cache, dan audit log.',
                'permissions' => [
                    'observability.*',
                    'security.logs.view',
                    'system.cache.manage',
                    'system.queue.monitor',
                    'system.troubleshoot.*',
                    'academic.view',
                    'finance.view',
                ],
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
