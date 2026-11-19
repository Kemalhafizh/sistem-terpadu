<?php

namespace Tests\Feature\Architecture;

use Tests\Support\RandomnessScanner;
use Tests\TestCase;

/**
 * Data comes from the database. A value made up with rand(), Faker or
 * ->random() and shown or stored as if it were real is not allowed in app/.
 *
 * - ALLOWED: randomness that is the feature itself (shuffling an exam,
 *   generating a secret). Listed per file and per call, with the reason.
 * - DEBT: made-up values that already existed when this guard arrived.
 *   Each must be replaced with real data (or an honest null/error) and then
 *   removed here. The counts are exact, so the list can only shrink.
 *
 * Anything else fails CI. See docs/CONTRIBUTING.md, "Tidak ada angka karangan".
 */
class NoFabricatedRandomnessTest extends TestCase
{
    /** @var array<string, array<string, string>> file => call => why it is legitimate */
    private const ALLOWED = [
        'app/Services/CbtService.php' => [
            'mt_srand' => 'Urutan soal dan opsi diacak deterministik per siswa (seed dari ujian + siswa).',
            'mt_rand' => 'Sama: Fisher-Yates dengan seed di atas.',
        ],
        'app/Services/QuestionBankService.php' => [
            '->inRandomOrder' => 'Memilih soal acak dari bank soal untuk paket ujian.',
        ],
        'app/Services/TotpSecurityService.php' => [
            'random_int' => 'Rahasia TOTP dari CSPRNG.',
        ],
        'app/Models/User.php' => [
            'random_int' => 'Kode 2FA dari CSPRNG.',
        ],
        'app/Services/Auth/AccountProvisioningService.php' => [
            'random_int' => 'Kata sandi sementara awal/reset akun dari CSPRNG.',
        ],
        'app/Services/Auth/WhatsappPasswordResetService.php' => [
            'random_int' => 'Kode OTP reset kata sandi WhatsApp 6 digit dari CSPRNG.',
        ],
    ];

    /** @var array<string, array<string, int>> file => call => how many remain */
    private const DEBT = [
    ];

    public function test_app_code_does_not_make_up_values_at_random(): void
    {
        $found = RandomnessScanner::scan(base_path(), app_path());
        $problems = [];

        foreach ($found as $file => $calls) {
            foreach ($calls as $call => $lines) {
                if (isset(self::ALLOWED[$file][$call])) {
                    continue;
                }
                $debt = self::DEBT[$file][$call] ?? 0;
                if (count($lines) > $debt) {
                    $problems[] = "{$file}: {$call}() baru di baris ".implode(', ', $lines).
                        '. Ambil nilainya dari database, atau kembalikan null/galat yang jujur.';
                } elseif (count($lines) < $debt) {
                    $problems[] = "{$file}: {$call}() tinggal ".count($lines)." dari {$debt}. Bagus, turunkan angkanya di DEBT.";
                }
            }
        }

        foreach ([self::ALLOWED, self::DEBT] as $list) {
            foreach ($list as $file => $calls) {
                foreach (array_keys($calls) as $call) {
                    if (! isset($found[$file][$call])) {
                        $problems[] = "{$file}: {$call}() sudah tidak ada. Hapus entrinya dari daftar.";
                    }
                }
            }
        }

        $this->assertSame([], $problems, "Nilai acak di app/:\n".implode("\n", $problems));
    }

    public function test_the_scanner_ignores_comments_strings_and_str_random(): void
    {
        $calls = RandomnessScanner::callsIn(<<<'PHP'
        <?php
        // rand(1, 2) in a comment
        $a = "rand(1, 2) in a string";
        $token = Str::random(40);
        $b = rand(1, 9);
        $c = collect([1, 2])->random();
        $d = $q->inRandomOrder()->get();
        $e = \Faker\Factory::create();
        function rand_label() {}
        PHP);

        $this->assertSame(['rand', '->random', '->inRandomOrder', 'Faker'], array_column($calls, 0));
    }
}
