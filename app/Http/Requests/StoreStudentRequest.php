<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id|unique:students',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'nis' => 'required|string|unique:students',
            'nisn' => 'required|string|unique:students',
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'address' => 'nullable|string',
            // Required by Dapodik; the export used to fill in "Semarang" and "Ibu Kandung Siswa".
            'birth_place' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:255',
        ];
    }
}
