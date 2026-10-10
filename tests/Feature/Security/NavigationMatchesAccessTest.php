<?php

use App\Models\User;

/*
 * The same app for everyone; only what each role may open differs. So a
 * menu must never show a link its user would be refused: every link in the
 * sidebar/top navigation is followed, as that role, and none may answer 403.
 * (It used to: the principal saw 12 menu items that all 403'd, a vice
 * principal 22 — the sidebar kept its own role lists.)
 */

function navigationLinks(string $html): array
{
    preg_match_all('#href="https?://[^/"]+(/[^"\#?]*)"#', $html, $m);

    return array_values(array_unique(array_filter(
        $m[1],
        fn ($path) => ! str_contains($path, 'logout') && ! str_starts_with($path, '/build/') && ! str_starts_with($path, '/storage/')
    )));
}

test('no menu link is refused to the role it is shown to', function (string $role) {
    $user = User::factory()->create(['role' => $role]);
    $html = $this->actingAs($user)->get('/dashboard')->getContent();

    $refused = [];
    foreach (navigationLinks($html) as $path) {
        if ($this->actingAs($user)->get($path)->getStatusCode() === 403) {
            $refused[] = $path;
        }
    }

    expect($refused)->toBe([]);
})->with(['superadmin', 'admin', 'kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas', 'guru', 'bk', 'staf_tu', 'parent', 'student', 'siswa', 'orang_tua', 'petugas_uks']);
