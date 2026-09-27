<?php

namespace App\Http\Controllers\Strategic;

use App\Http\Controllers\Controller;
use App\Models\SatisfactionQuestion;
use App\Models\SatisfactionResponse;
use App\Models\SatisfactionSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SatisfactionSurveyController extends Controller
{
    public const TYPES = [
        'ikm_siswa' => 'IKM Siswa',
        'ikm_orangtua' => 'IKM Orang Tua',
        'ikm_guru' => 'IKM Guru & Karyawan',
        'ikm_mitra' => 'IKM Mitra',
        'custom' => 'Lainnya',
    ];

    public const SCALES = [
        'likert_5' => 'Skala 1–5',
        'likert_7' => 'Skala 1–7',
        'yes_no' => 'Ya / Tidak',
        'open_text' => 'Isian bebas',
    ];

    /** Highest score per scale, to express averages as a 0–100 index. */
    public const SCALE_MAX = ['likert_5' => 5, 'likert_7' => 7, 'yes_no' => 1];

    public function index()
    {
        $surveys = SatisfactionSurvey::withCount('dimensions')
            ->withCount(['responses as respondents_count' => fn ($q) => $q->select(DB::raw('count(distinct respondent_id)'))])
            ->latest()->get();

        return view('strategic.survey.index', compact('surveys'));
    }

    public function create()
    {
        return view('strategic.survey.create', ['types' => self::TYPES, 'scales' => self::SCALES]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'survey_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_anonymous' => 'boolean',
            'status' => 'required|in:draft,active',
            'questions' => 'required|array|min:1',
            'questions.*.dimension' => 'required|string|max:100',
            'questions.*.text' => 'required|string|max:1000',
            'questions.*.scale' => ['required', Rule::in(array_keys(self::SCALES))],
        ], [
            'questions.required' => 'Tambahkan minimal satu pertanyaan.',
        ]);

        $survey = DB::transaction(function () use ($validated, $request) {
            $survey = SatisfactionSurvey::create([
                'title' => $validated['title'],
                'survey_type' => $validated['survey_type'],
                'description' => $validated['description'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_anonymous' => $request->boolean('is_anonymous'),
                'status' => $validated['status'],
                'created_by' => auth()->id(),
            ]);

            // Questions are grouped into dimensions by the name typed for each.
            $dimensions = [];
            foreach (array_values($validated['questions']) as $i => $question) {
                $name = trim($question['dimension']);
                $dimensions[$name] ??= $survey->dimensions()->create(['dimension_name' => $name, 'sort_order' => count($dimensions) + 1]);
                $dimensions[$name]->questions()->create([
                    'question_text' => $question['text'],
                    'scale_type' => $question['scale'],
                    'sort_order' => $i + 1,
                ]);
            }

            return $survey;
        });

        return redirect()->route('strategic.survey.show', $survey->id)->with('success', 'Survei Kepuasan berhasil dibuat.');
    }

    public function show($id)
    {
        $survey = SatisfactionSurvey::with('dimensions.questions')->findOrFail($id);
        $respondents = $survey->responses()->distinct('respondent_id')->count('respondent_id');
        $scales = self::SCALES;

        return view('strategic.survey.show', compact('survey', 'respondents', 'scales'));
    }

    public function respond($id)
    {
        $survey = SatisfactionSurvey::with('dimensions.questions')->findOrFail($id);
        $alreadyAnswered = $survey->responses()->where('respondent_id', auth()->id())->exists();

        return view('strategic.survey.respond', compact('survey', 'alreadyAnswered'));
    }

    public function storeResponse(Request $request, $id)
    {
        $survey = SatisfactionSurvey::with('dimensions.questions')->findOrFail($id);

        // This used to redirect with a success message without saving anything.
        abort_unless($survey->isOpen(), 403, 'Survei ini sedang tidak menerima tanggapan.');

        if ($survey->responses()->where('respondent_id', auth()->id())->exists()) {
            return redirect()->route('strategic.survey.respond', $survey->id)->with('info', 'Anda sudah mengisi survei ini. Terima kasih!');
        }

        $questions = $survey->dimensions->flatMap->questions;
        $validated = $request->validate($questions->mapWithKeys(fn (SatisfactionQuestion $q) => [
            "answers.{$q->id}" => match ($q->scale_type) {
                'likert_5' => 'required|integer|between:1,5',
                'likert_7' => 'required|integer|between:1,7',
                'yes_no' => 'required|in:0,1',
                default => 'nullable|string|max:2000',
            },
        ])->all(), [
            'answers.*.required' => 'Pertanyaan ini wajib dijawab.',
        ]);

        DB::transaction(function () use ($survey, $questions, $validated) {
            foreach ($questions as $question) {
                $answer = $validated['answers'][$question->id] ?? null;
                if ($question->scale_type === 'open_text' && blank($answer)) {
                    continue;
                }

                // respondent_id is kept even for anonymous surveys so a person
                // can answer only once; results never show who answered what.
                SatisfactionResponse::create([
                    'survey_id' => $survey->id,
                    'question_id' => $question->id,
                    'respondent_id' => auth()->id(),
                    'score' => $question->scale_type === 'open_text' ? null : (int) $answer,
                    'text_response' => $question->scale_type === 'open_text' ? $answer : null,
                ]);
            }
        });

        return redirect()->route('strategic.survey.respond', $survey->id)->with('success', 'Tanggapan survei berhasil disimpan. Terima kasih!');
    }

    public function results($id)
    {
        $survey = SatisfactionSurvey::with('dimensions.questions.responses')->findOrFail($id);
        $respondents = $survey->responses()->distinct('respondent_id')->count('respondent_id');

        $dimensions = $survey->dimensions->map(function ($dimension) {
            $questions = $dimension->questions->map(function (SatisfactionQuestion $q) {
                $scores = $q->responses->whereNotNull('score')->pluck('score');
                $max = self::SCALE_MAX[$q->scale_type] ?? null;

                return [
                    'question' => $q,
                    'count' => $q->responses->count(),
                    'average' => $scores->isNotEmpty() ? round($scores->avg(), 2) : null,
                    'index' => $max && $scores->isNotEmpty() ? round($scores->avg() / $max * 100, 1) : null,
                    'distribution' => $scores->countBy()->sortKeys(),
                    'texts' => $q->responses->whereNotNull('text_response')->pluck('text_response'),
                ];
            });

            $indexes = $questions->pluck('index')->filter(fn ($i) => $i !== null);

            return ['dimension' => $dimension, 'questions' => $questions, 'index' => $indexes->isNotEmpty() ? round($indexes->avg(), 1) : null];
        });

        $overall = $dimensions->pluck('index')->filter(fn ($i) => $i !== null);
        $overallIndex = $overall->isNotEmpty() ? round($overall->avg(), 1) : null;

        return view('strategic.survey.results', compact('survey', 'respondents', 'dimensions', 'overallIndex'));
    }
}
