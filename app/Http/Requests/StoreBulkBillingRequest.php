<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBulkBillingRequest extends FormRequest
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
            'payment_category_id' => 'required|exists:payment_categories,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'month' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ];
    }
}
