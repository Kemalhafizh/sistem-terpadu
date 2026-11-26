<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningLetter extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Default net point balance thresholds when the school has not customized
     * discipline thresholds in `settings` (`ALUR_KERJA.md` E1).
     */
    public const THRESHOLDS = [
        'SP1' => 50,
        'SP2' => 75,
        'SP3' => 100,
    ];

    public const DEFAULT_STAGE_LABELS = [
        'SP1' => 'SP1 (Waspada)',
        'SP2' => 'SP2 (Kritis)',
        'SP3' => 'SP3 (Sangat Kritis)',
    ];

    public const DEFAULT_ATTENTION_THRESHOLD = 30;

    protected $fillable = [
        'student_id',
        'level',
        'point_threshold',
        'issued_at',
        'issued_by',
        'signed_at',
        'signed_by_parent_id',
        'digital_signature',
        'notes',
        'counseling_session_id',
        'parent_notified_at',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'signed_at' => 'datetime',
        'parent_notified_at' => 'datetime',
    ];

    /**
     * Resolve configured SP stages & thresholds from `settings` (`group = discipline`),
     * falling back to `self::THRESHOLDS` when unconfigured. Always sorted ascending by threshold.
     *
     * @return array<string, int>
     */
    public static function thresholds(): array
    {
        $rawStages = Setting::get('discipline_warning_stages');
        if (is_string($rawStages) && $rawStages !== '') {
            $decoded = json_decode($rawStages, true);
            if (is_array($decoded) && $decoded !== []) {
                $parsed = [];
                foreach ($decoded as $key => $item) {
                    if (is_array($item) && isset($item['level'], $item['threshold']) && is_numeric($item['threshold'])) {
                        $level = strtoupper(trim((string) $item['level']));
                        $val = max(1, (int) $item['threshold']);
                        if ($level !== '') {
                            $parsed[$level] = $val;
                        }
                    } elseif (is_string($key) && is_numeric($item)) {
                        $parsed[strtoupper(trim($key))] = max(1, (int) $item);
                    }
                }

                if ($parsed !== []) {
                    asort($parsed);

                    return $parsed;
                }
            }
        }

        $sp1 = Setting::get('discipline_sp1_threshold');
        $sp2 = Setting::get('discipline_sp2_threshold');
        $sp3 = Setting::get('discipline_sp3_threshold');

        $thresholds = [
            'SP1' => is_numeric($sp1) && (int) $sp1 > 0 ? (int) $sp1 : self::THRESHOLDS['SP1'],
            'SP2' => is_numeric($sp2) && (int) $sp2 > 0 ? (int) $sp2 : self::THRESHOLDS['SP2'],
            'SP3' => is_numeric($sp3) && (int) $sp3 > 0 ? (int) $sp3 : self::THRESHOLDS['SP3'],
        ];

        asort($thresholds);

        return $thresholds;
    }

    /**
     * Resolve human-readable status labels per SP level from `settings` or defaults.
     *
     * @return array<string, string>
     */
    public static function stageLabels(): array
    {
        $labels = self::DEFAULT_STAGE_LABELS;
        $rawStages = Setting::get('discipline_warning_stages');

        if (is_string($rawStages) && $rawStages !== '') {
            $decoded = json_decode($rawStages, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_array($item) && isset($item['level'], $item['label']) && filled($item['label'])) {
                        $labels[strtoupper(trim((string) $item['level']))] = trim((string) $item['label']);
                    }
                }
            }
        }

        foreach (array_keys(self::thresholds()) as $level) {
            if (! isset($labels[$level])) {
                $labels[$level] = $level;
            }
        }

        return $labels;
    }

    /**
     * Point threshold for early warning status ("Perlu Perhatian") before SP1.
     */
    public static function attentionThreshold(): int
    {
        $configured = Setting::get('discipline_attention_threshold');
        $thresholds = self::thresholds();
        $firstSp = reset($thresholds) ?: self::THRESHOLDS['SP1'];

        if (is_numeric($configured) && (int) $configured > 0) {
            return min((int) $configured, max(1, $firstSp - 1));
        }

        return min(self::DEFAULT_ATTENTION_THRESHOLD, max(1, $firstSp - 1));
    }

    /**
     * Point threshold at which mandatory BK counseling (E2) is automatically scheduled.
     * Defaults to the lowest SP threshold (SP1) when not overridden in `settings`.
     */
    public static function mandatoryCounselingThreshold(): int
    {
        $configured = Setting::get('discipline_mandatory_counseling_threshold');
        if (is_numeric($configured) && (int) $configured > 0) {
            return (int) $configured;
        }

        $thresholds = self::thresholds();

        return (int) (reset($thresholds) ?: self::THRESHOLDS['SP1']);
    }

    /**
     * Compute human-readable discipline status label for a net point total.
     */
    public static function statusForPoints(int $totalPoints): string
    {
        $thresholds = self::thresholds();
        $labels = self::stageLabels();

        $descending = $thresholds;
        arsort($descending);

        foreach ($descending as $level => $threshold) {
            if ($totalPoints >= $threshold) {
                return $labels[$level] ?? $level;
            }
        }

        if ($totalPoints >= self::attentionThreshold()) {
            return 'Perlu Perhatian';
        }

        return 'Aman';
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /** @return BelongsTo<User, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by_parent_id');
    }

    /** @return BelongsTo<User, $this> */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /** @return BelongsTo<CounselingSession, $this> */
    public function counselingSession(): BelongsTo
    {
        return $this->belongsTo(CounselingSession::class, 'counseling_session_id');
    }
}
