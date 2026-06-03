<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMutationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:in,out',
            'mutation_date' => 'required|date',
            'reason' => 'required|string|max:255',
            'target_school' => 'nullable|string|max:255',
            'document' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ];
    }
}
