<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\TahfidzLog;
use Illuminate\Http\Request;

class TahfidzController extends Controller
{
    /**
     * Display listing for a specific student (for teachers/parents).
     */
    public function show(Student $student)
    {
        $user = auth()->user();

        // Ownership Check
        if ($user->role === 'student') {
            if ($student->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke data hafalan ini.');
            }
        } elseif ($user->role === 'parent') {
            // Assuming parent model has children or using student->parent_id
            if ($student->user_id !== $user->id && $student->parent_id !== $user->id) {
                // Adjust based on actual parent-student relationship in the model
                // For now, let's check if there's a parent_id on the student
                if (property_exists($student, 'parent_id') && $student->parent_id !== $user->id) {
                    abort(403, 'Anda tidak memiliki akses ke data hafalan anak ini.');
                }
            }
        }

        $logs = $student->tahfidzLogs()->with('teacher')->latest()->get();
        $surahs = $this->getSurahs();

        return view('tahfidz.show', compact('student', 'logs', 'surahs'));
    }

    private function getSurahs()
    {
        return [
            1 => 'Al-Fatihah', 2 => 'Al-Baqarah', 3 => 'Ali \'Imran', 4 => 'An-Nisa\'', 5 => 'Al-Ma\'idah',
            6 => 'Al-An\'am', 7 => 'Al-A\'raf', 8 => 'Al-Anfal', 9 => 'At-Taubah', 10 => 'Yunus',
            11 => 'Hud', 12 => 'Yusuf', 13 => 'Ar-Ra\'d', 14 => 'Ibrahim', 15 => 'Al-Hijr',
            16 => 'An-Nahl', 17 => 'Al-Isra\'', 18 => 'Al-Kahf', 19 => 'Maryam', 20 => 'Ta Ha',
            21 => 'Al-Anbiya\'', 22 => 'Al-Hajj', 23 => 'Al-Mu\'minun', 24 => 'An-Nur', 25 => 'Al-Furqan',
            26 => 'Asy-Syu\'ara\'', 27 => 'An-Naml', 28 => 'Al-Qasas', 29 => 'Al-\'Ankabut', 30 => 'Ar-Rum',
            31 => 'Luqman', 32 => 'As-Sajdah', 33 => 'Al-Ahzab', 34 => 'Saba\'', 35 => 'Fatir',
            36 => 'Ya Sin', 37 => 'As-Saffat', 38 => 'Sad', 39 => 'Az-Zumar', 40 => 'Ghafir',
            41 => 'Fussilat', 42 => 'Asy-Syura', 43 => 'Az-Zukhruf', 44 => 'Ad-Dukhan', 45 => 'Al-Jasiyah',
            46 => 'Al-Ahqaf', 47 => 'Muhammad', 48 => 'Al-Fath', 49 => 'Al-Hujurat', 50 => 'Qaf',
            51 => 'Az-Zariyat', 52 => 'At-Tur', 53 => 'An-Najm', 54 => 'Al-Qamar', 55 => 'Ar-Rahman',
            56 => 'Al-Waqi\'ah', 57 => 'Al-Hadid', 58 => 'Al-Mujadilah', 59 => 'Al-Hasyr', 60 => 'Al-Mumtahanah',
            61 => 'As-Saff', 62 => 'Al-Jumu\'ah', 63 => 'Al-Munafiqun', 64 => 'At-Tagabun', 65 => 'At-Talaq',
            66 => 'At-Tahrim', 67 => 'Al-Mulk', 68 => 'Al-Qalam', 69 => 'Al-Haqqah', 70 => 'Al-Ma\'arij',
            71 => 'Nuh', 72 => 'Al-Jinn', 73 => 'Al-Muzzammil', 74 => 'Al-Muddassir', 75 => 'Al-Qiyamah',
            76 => 'Al-Insan', 77 => 'Al-Mursalat', 78 => 'An-Naba\'', 79 => 'An-Nazi\'at', 80 => 'Abasa',
            81 => 'At-Takwir', 82 => 'Al-Infitar', 83 => 'Al-Mutaffifin', 84 => 'Al-Insyiqaq', 85 => 'Al-Buruj',
            86 => 'At-Tariq', 87 => 'Al-A\'la', 88 => 'Al-Gasyiyah', 89 => 'Al-Fajr', 90 => 'Al-Balad',
            91 => 'Asy-Syams', 92 => 'Al-Lail', 93 => 'Ad-Duha', 94 => 'Asy-Syarh', 95 => 'At-Tin',
            96 => 'Al-\'Alaq', 97 => 'Al-Qadr', 98 => 'Al-Bayyinah', 99 => 'Az-Zalzalah', 100 => 'Al-\'Adiyat',
            101 => 'Al-Qari\'ah', 102 => 'At-Takasur', 103 => 'Al-\'Asr', 104 => 'Al-Humazah', 105 => 'Al-Fil',
            106 => 'Quraisy', 107 => 'Al-Ma\'un', 108 => 'Al-Kausar', 109 => 'Al-Kafirun', 110 => 'An-Nasr',
            111 => 'Al-Lahab', 112 => 'Al-Ikhlas', 113 => 'Al-Falaq', 114 => 'An-Nas',
        ];
    }

    /**
     * Store a new memorization log.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'surah_number' => 'required|integer|min:1|max:114',
            'surah_name' => 'required|string',
            'from_ayat' => 'required|integer|min:1',
            'to_ayat' => 'required|integer|min:1',
            'type' => 'required|in:ziyadah,murajaah',
            'grade' => 'nullable|string',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $validated['teacher_id'] = auth()->id();

        TahfidzLog::create($validated);

        return back()->with('success', 'Setoran hafalan berhasil dicatat!');
    }
}
