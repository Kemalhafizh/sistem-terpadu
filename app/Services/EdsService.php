<?php

namespace App\Services;

use App\Models\EdsPeriod;
use App\Models\EdsStandard;
use App\Models\EdsIndicator;
use App\Models\EdsAssessment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class EdsService
{
    /**
     * Seed default 8 SNP standards and indicators if tables are empty.
     */
    public function seedDefaultStandards(): void
    {
        if (EdsStandard::count() > 0) {
            return;
        }

        $standards = [
            [
                'code' => 'SNP-1',
                'name' => 'Standar Kompetensi Lulusan',
                'description' => 'Kualifikasi kemampuan lulusan yang mencakup sikap, pengetahuan, dan keterampilan.',
                'order' => 1,
                'indicators' => [
                    [
                        'code' => '1.1',
                        'name' => 'Lulusan memiliki kompetensi pada dimensi sikap',
                        'description' => 'Perilaku yang mencerminkan sikap beriman, berakhlak mulia, berilmu, percaya diri, dan bertanggung jawab.',
                        'order' => 1,
                    ],
                    [
                        'code' => '1.2',
                        'name' => 'Lulusan memiliki kompetensi pada dimensi pengetahuan',
                        'description' => 'Memiliki pengetahuan faktual, konseptual, prosedural, dan metakognitif.',
                        'order' => 2,
                    ],
                    [
                        'code' => '1.3',
                        'name' => 'Lulusan memiliki kompetensi pada dimensi keterampilan',
                        'description' => 'Memiliki kemampuan berpikir dan bertindak kreatif, produktif, kritis, mandiri, kolaboratif, dan komunikatif.',
                        'order' => 3,
                    ]
                ]
            ],
            [
                'code' => 'SNP-2',
                'name' => 'Standar Isi',
                'description' => 'Ruang lingkup materi dan tingkat kompetensi untuk mencapai kompetensi lulusan.',
                'order' => 2,
                'indicators' => [
                    [
                        'code' => '2.1',
                        'name' => 'Kurikulum tingkat satuan pendidikan dikembangkan sesuai prosedur',
                        'description' => 'Melibatkan pemangku kepentingan dalam pengembangan kurikulum sekolah.',
                        'order' => 1,
                    ],
                    [
                        'code' => '2.2',
                        'name' => 'Kurikulum tingkat satuan pendidikan memuat karakteristik dan muatan lokal',
                        'description' => 'Menyediakan materi pelajaran bermuatan lokal dan pembinaan karakter yang khas.',
                        'order' => 2,
                    ]
                ]
            ],
            [
                'code' => 'SNP-3',
                'name' => 'Standar Proses',
                'description' => 'Pelaksanaan pembelajaran pada satuan pendidikan untuk mencapai standar kompetensi lulusan.',
                'order' => 3,
                'indicators' => [
                    [
                        'code' => '3.1',
                        'name' => 'Perencanaan pembelajaran dilakukan secara menyeluruh',
                        'description' => 'Penyusunan RPP dan silabus lengkap oleh seluruh staf pendidik.',
                        'order' => 1,
                    ],
                    [
                        'code' => '3.2',
                        'name' => 'Pelaksanaan pembelajaran mengedepankan interaksi aktif',
                        'description' => 'Proses belajar mengajar yang interaktif, inspiratif, menyenangkan, menantang, dan memotivasi.',
                        'order' => 2,
                    ],
                    [
                        'code' => '3.3',
                        'name' => 'Pengawasan proses pembelajaran dilakukan secara berkala',
                        'description' => 'Pemantauan, supervisi, evaluasi, pelaporan, dan tindak lanjut pengawasan.',
                        'order' => 3,
                    ]
                ]
            ],
            [
                'code' => 'SNP-4',
                'name' => 'Standar Penilaian Pendidikan',
                'description' => 'Mekanisme, prosedur, dan instrumen penilaian hasil belajar siswa.',
                'order' => 4,
                'indicators' => [
                    [
                        'code' => '4.1',
                        'name' => 'Penilaian hasil belajar dilakukan secara objektif, adil, dan transparan',
                        'description' => 'Prosedur penilaian didasarkan pada standar penilaian yang disosialisasikan secara terbuka.',
                        'order' => 1,
                    ],
                    [
                        'code' => '4.2',
                        'name' => 'Instrumen penilaian hasil belajar memenuhi persyaratan mutu',
                        'description' => 'Kesesuaian kisi-kisi soal dengan kompetensi dasar yang diujikan.',
                        'order' => 2,
                    ]
                ]
            ],
            [
                'code' => 'SNP-5',
                'name' => 'Standar Pendidik dan Tenaga Kependidikan',
                'description' => 'Kualifikasi akademik dan kompetensi yang harus dipenuhi oleh guru dan staf.',
                'order' => 5,
                'indicators' => [
                    [
                        'code' => '5.1',
                        'name' => 'Ketersediaan dan kualifikasi guru sesuai bidang tugas',
                        'description' => 'Kualifikasi akademik minimum guru adalah Sarjana (S1/D4) dan bersesuaian bidang studi.',
                        'order' => 1,
                    ],
                    [
                        'code' => '5.2',
                        'name' => 'Kompetensi pendidik dan tenaga kependidikan bersertifikasi standar nasional',
                        'description' => 'Memiliki sertifikat pendidik untuk menjamin kompetensi profesional.',
                        'order' => 2,
                    ]
                ]
            ],
            [
                'code' => 'SNP-6',
                'name' => 'Standar Sarana dan Prasarana',
                'description' => 'Kebutuhan minimal tentang ruang belajar, tempat berolahraga, tempat ibadah, dll.',
                'order' => 6,
                'indicators' => [
                    [
                        'code' => '6.1',
                        'name' => 'Sarana pembelajaran di kelas lengkap dan layak pakai',
                        'description' => 'Ketersediaan papan tulis, proyektor, meja-kursi, ventilasi, dan penerangan.',
                        'order' => 1,
                    ],
                    [
                        'code' => '6.2',
                        'name' => 'Prasarana sekolah memenuhi standar kapasitas',
                        'description' => 'Laboratorium IPA, laboratorium komputer, perpustakaan, UKS, dan sarana ibadah memadai.',
                        'order' => 2,
                    ]
                ]
            ],
            [
                'code' => 'SNP-7',
                'name' => 'Standar Pengelolaan',
                'description' => 'Perencanaan, pelaksanaan, dan pengawasan kegiatan pendidikan di sekolah.',
                'order' => 7,
                'indicators' => [
                    [
                        'code' => '7.1',
                        'name' => 'Pengelolaan sekolah transparan, akuntabel, dan berbasis sistem informasi',
                        'description' => 'Pemanfaatan sistem informasi sekolah terpadu untuk efisiensi administrasi.',
                        'order' => 1,
                    ],
                    [
                        'code' => '7.2',
                        'name' => 'Keterlibatan pemangku kepentingan sekolah berjalan harmonis',
                        'description' => 'Hubungan kerja sama yang sinergis dengan komite sekolah dan orang tua.',
                        'order' => 2,
                    ]
                ]
            ],
            [
                'code' => 'SNP-8',
                'name' => 'Standar Pembiayaan',
                'description' => 'Ketentuan mengenai biaya operasional, personal, dan investasi sekolah.',
                'order' => 8,
                'indicators' => [
                    [
                        'code' => '8.1',
                        'name' => 'Pengalokasian anggaran operasional dan investasi transparan',
                        'description' => 'Penyusunan Rencana Kegiatan dan Anggaran Sekolah (RKAS) yang akuntabel.',
                        'order' => 1,
                    ],
                    [
                        'code' => '8.2',
                        'name' => 'Pelaporan pertanggungjawaban keuangan sekolah tepat waktu',
                        'description' => 'Penyusunan laporan bulanan, semesteran, dan tahunan yang diaudit berkala.',
                        'order' => 2,
                    ]
                ]
            ],
        ];

        DB::transaction(function () use ($standards) {
            foreach ($standards as $stdData) {
                $indicators = $stdData['indicators'];
                unset($stdData['indicators']);

                $standard = EdsStandard::create($stdData);

                foreach ($indicators as $indData) {
                    $indData['standard_id'] = $standard->id;
                    EdsIndicator::create($indData);
                }
            }
        });
    }

    /**
     * Compute average scores for all 8 standards in a given period.
     */
    public function getStandardScores(EdsPeriod $period): array
    {
        $this->seedDefaultStandards();

        $standards = EdsStandard::with('indicators')->get();
        $assessments = EdsAssessment::where('period_id', $period->id)->get()->keyBy('indicator_id');

        $scores = [];
        $totalAssessedScore = 0;
        $totalIndicatorsCount = 0;
        $assessedIndicatorsCount = 0;

        foreach ($standards as $std) {
            $stdSum = 0;
            $stdIndicatorCount = $std->indicators->count();
            $totalIndicatorsCount += $stdIndicatorCount;
            $assessedCount = 0;

            foreach ($std->indicators as $ind) {
                if (isset($assessments[$ind->id])) {
                    $score = $assessments[$ind->id]->score;
                    $stdSum += $score;
                    $totalAssessedScore += $score;
                    $assessedCount++;
                    $assessedIndicatorsCount++;
                }
            }

            // Average score is calculated based on total indicators for standard (unassessed count as 0)
            $avgScore = $stdIndicatorCount > 0 ? round($stdSum / $stdIndicatorCount, 2) : 0;

            $scores[$std->code] = [
                'standard' => $std,
                'average' => $avgScore,
                'assessed_count' => $assessedCount,
                'total_count' => $stdIndicatorCount,
                'progress_percentage' => $stdIndicatorCount > 0 ? round(($assessedCount / $stdIndicatorCount) * 100) : 0
            ];
        }

        $overallAverage = $totalIndicatorsCount > 0 ? round($totalAssessedScore / $totalIndicatorsCount, 2) : 0;
        $overallProgress = $totalIndicatorsCount > 0 ? round(($assessedIndicatorsCount / $totalIndicatorsCount) * 100) : 0;

        return [
            'standards' => $scores,
            'overall_average' => $overallAverage,
            'overall_progress' => $overallProgress,
            'total_indicators' => $totalIndicatorsCount,
            'assessed_indicators' => $assessedIndicatorsCount,
        ];
    }

    /**
     * Format data for ApexCharts radar/spider chart.
     */
    public function getSpiderChartData(EdsPeriod $period): array
    {
        $scoresData = $this->getStandardScores($period);
        
        $categories = [];
        $seriesData = [];

        foreach ($scoresData['standards'] as $code => $data) {
            $categories[] = $data['standard']->name;
            $seriesData[] = $data['average'];
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Skor Mutu',
                    'data' => $seriesData
                ]
            ]
        ];
    }

    /**
     * Fetch assessments with low scores (score < 3) for Gap Analysis.
     */
    public function getGapAnalysis(EdsPeriod $period): array
    {
        $this->seedDefaultStandards();

        $indicators = EdsIndicator::with('standard')->get();
        $assessments = EdsAssessment::with('assessor')->where('period_id', $period->id)->get()->keyBy('indicator_id');

        $gaps = [];

        foreach ($indicators as $ind) {
            $assessment = $assessments[$ind->id] ?? null;
            $score = $assessment ? $assessment->score : 0; // Unassessed counts as 0, which is < 3

            if ($score < 3) {
                $gaps[] = [
                    'indicator' => $ind,
                    'standard' => $ind->standard,
                    'score' => $score,
                    'evidence' => $assessment ? $assessment->evidence : 'Belum diisi/dievaluasi.',
                    'recommendation' => $assessment ? $assessment->recommendation : 'Segera lakukan penilaian awal dan susun rencana perbaikan.',
                    'assessed_by' => $assessment ? $assessment->assessor?->name : null,
                    'is_assessed' => !is_null($assessment)
                ];
            }
        }

        return $gaps;
    }

    /**
     * Format comparison data between two periods.
     */
    public function getComparisonData(EdsPeriod $current, EdsPeriod $previous): array
    {
        $currentScores = $this->getStandardScores($current);
        $previousScores = $this->getStandardScores($previous);

        $comparison = [];

        foreach ($currentScores['standards'] as $code => $currData) {
            $prevVal = $previousScores['standards'][$code]['average'] ?? 0;
            $currVal = $currData['average'];
            
            $difference = round($currVal - $prevVal, 2);

            $comparison[$code] = [
                'name' => $currData['standard']->name,
                'previous_score' => $prevVal,
                'current_score' => $currVal,
                'difference' => $difference,
            ];
        }

        return [
            'standards' => $comparison,
            'current_period_name' => $current->name,
            'previous_period_name' => $previous->name,
            'current_overall_average' => $currentScores['overall_average'],
            'previous_overall_average' => $previousScores['overall_average'],
            'overall_difference' => round($currentScores['overall_average'] - $previousScores['overall_average'], 2)
        ];
    }

    /**
     * Generate elegant PDF report.
     */
    public function generateReportPdf(EdsPeriod $period)
    {
        $scoresData = $this->getStandardScores($period);
        $gapData = $this->getGapAnalysis($period);

        $data = [
            'period' => $period,
            'academicYear' => $period->academicYear,
            'scoresData' => $scoresData,
            'gapData' => $gapData,
            'schoolName' => school_name(),
            'schoolAddress' => school_address(),
            'schoolContact' => school_contact_line(['phone', 'email']),
            'generatedAt' => now()->format('d-m-Y H:i')
        ];

        // Configure dompdf and load HTML
        $pdf = Pdf::loadView('eds.report-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false);

        return $pdf;
    }
}
