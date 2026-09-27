<?php

$locales = ['en', 'ar'];
$baseDir = 'lang/id';
$files = glob("$baseDir/*.php");

echo "=== TRANSLATION AUDIT START ===\n";

foreach ($files as $file) {
    $filename = basename($file);
    $idData = include $file;

    echo "\nChecking file: $filename\n";

    foreach ($locales as $loc) {
        $locFile = "lang/$loc/$filename";
        if (! file_exists($locFile)) {
            echo "[-] File missing: $locFile\n";

            continue;
        }

        $locData = include $locFile;

        // 1. Check for missing keys
        $missing = array_diff_key($idData, $locData);
        if (! empty($missing)) {
            echo "[-] Missing keys in $locFile: ".implode(', ', array_keys($missing))."\n";
        } else {
            echo "[+] All keys present in $locFile\n";
        }

        // 2. Check for identical values (untranslated)
        $untranslated = [];
        $allowedIdentical = [
            'dashboard', 'academic', 'BK', 'EDS', 'CBT', 'HRD', 'UKS',
            'OSIS', 'MPK', 'SISTA', 'SPP', 'PDF', 'excel', 'import',
            'export', 'FAQ', 'Google', 'SSO', 'API', 'E-Rapor Digital',
            'Smart Library', 'E-Learning & Tugas', 'Dashboard',
        ];

        foreach ($idData as $key => $val) {
            if (is_array($val)) {
                // Check nested array
                foreach ($val as $subKey => $subVal) {
                    if (is_array($subVal)) {
                        continue;
                    }
                    if (isset($locData[$key][$subKey]) && $locData[$key][$subKey] === $subVal) {
                        if (! in_array($subVal, $allowedIdentical)) {
                            $untranslated[] = "$key.$subKey ('$subVal')";
                        }
                    }
                }

                continue;
            }
            if (isset($locData[$key]) && $locData[$key] === $val) {
                if (! in_array($val, $allowedIdentical)) {
                    $untranslated[] = "$key ('$val')";
                }
            }
        }

        if (! empty($untranslated)) {
            echo "[-] Untranslated keys in $locFile (matches ID): \n    ".implode("\n    ", $untranslated)."\n";
        }
    }
}

echo "\n=== TRANSLATION AUDIT END ===\n";
