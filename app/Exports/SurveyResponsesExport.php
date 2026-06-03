<?php

namespace App\Exports;

use App\Models\Survey;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SurveyResponsesExport implements FromCollection, WithHeadings, WithTitle
{
    protected $survey;

    public function __construct(Survey $survey)
    {
        // Load relationships safely
        $this->survey = $survey->load('questions', 'responses.user');
    }

    public function collection()
    {
        $data = collect();
        $questions = $this->survey->questions;
        $responses = $this->survey->responses;

        $counter = 1;
        foreach ($responses as $response) {
            $row = [];

            // Add identity columns based on anonymity status
            if ($this->survey->is_anonymous) {
                $row['nama'] = 'Responden Anonim #' . $counter++;
                $row['email'] = 'anonim@sultanagung1.sch.id';
            } else {
                $row['nama'] = $response->user ? $response->user->name : 'N/A';
                $row['email'] = $response->user ? $response->user->email : 'N/A';
            }

            // Add columns for each question
            $answers = $response->answers ?? [];
            foreach ($questions as $question) {
                $ansVal = $answers[$question->id] ?? null;

                if (is_array($ansVal)) {
                    $row['q_' . $question->id] = implode(', ', $ansVal);
                } else {
                    $row['q_' . $question->id] = $ansVal !== null ? (string)$ansVal : '-';
                }
            }

            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = [
            'Nama Responden',
            'Email Responden',
        ];

        foreach ($this->survey->questions as $question) {
            $headings[] = $question->question_text;
        }

        return $headings;
    }

    public function title(): string
    {
        return 'Respons ' . substr($this->survey->title, 0, 20);
    }
}
