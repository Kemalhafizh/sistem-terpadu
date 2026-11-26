<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMutationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isTransferInNew = $this->input('type') === 'in' && ! $this->filled('student_id');

        return [
            'type' => ['required', Rule::in(['in', 'out'])],
            'student_id' => [
                Rule::requiredIf(fn () => $this->input('type') === 'out'),
                'nullable',
                'exists:students,id',
            ],
            'classroom_id' => [
                Rule::requiredIf(fn () => $this->input('type') === 'in'),
                'nullable',
                'exists:classrooms,id',
            ],
            'school_year_id' => ['nullable', 'exists:school_years,id'],
            'mutation_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:255'],
            'target_school' => ['nullable', 'string', 'max:255'],
            'letter_number' => ['nullable', 'string', 'max:120'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],

            // Transfer-in candidate bio when student record does not exist yet
            'student_name' => [$isTransferInNew ? 'required' : 'nullable', 'string', 'max:180'],
            'nis' => [
                $isTransferInNew ? 'required' : 'nullable',
                'string',
                'max:32',
                Rule::unique('students', 'nis'),
            ],
            'nisn' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('students', 'nisn'),
            ],
            'nik' => ['nullable', 'string', 'max:32'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'birth_place' => ['nullable', 'string', 'max:120'],
            'birth_date' => ['nullable', 'date'],
            'mother_name' => ['nullable', 'string', 'max:180'],
            'address' => ['nullable', 'string', 'max:500'],
            'student_email' => ['nullable', 'email', 'max:180'],
            'student_phone' => ['nullable', 'string', 'max:32'],
            'parent_name' => ['nullable', 'string', 'max:180'],
            'parent_phone' => ['nullable', 'string', 'max:32'],
            'parent_email' => ['nullable', 'email', 'max:180'],
        ];
    }
}
