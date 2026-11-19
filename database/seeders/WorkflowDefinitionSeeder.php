<?php

namespace Database\Seeders;

use App\Models\WorkflowDefinition;
use Illuminate\Database\Seeder;

class WorkflowDefinitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $definitions = [
            [
                'name' => 'Persetujuan Pengajuan Cuti',
                'code' => 'leave_approval',
                'description' => 'Alur persetujuan permohonan cuti guru dan staf. Melalui Waka Kurikulum kemudian Kepala Sekolah.',
                'steps' => [
                    ['order' => 1, 'role' => 'waka_kurikulum'],
                    ['order' => 2, 'role' => 'kepala_sekolah'],
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Persetujuan Surat Resmi',
                'code' => 'letter_approval',
                'description' => 'Alur persetujuan surat keluar sekolah oleh Kepala Sekolah sebelum diterbitkan Staf TU.',
                'steps' => [
                    ['order' => 1, 'role' => 'kepala_sekolah', 'sla_hours' => 48],
                ],
                'is_active' => true,
            ],
        ];

        foreach ($definitions as $def) {
            WorkflowDefinition::updateOrCreate(
                ['code' => $def['code']],
                $def
            );
        }
    }
}
