<?php

namespace App\Http\Requests;

use App\Services\Auth\UserIdentifierResolver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->input('role') === 'superadmin') {
            return $this->user()?->role === 'superadmin';
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        $email = trim((string) $this->input('email', ''));
        $phone = trim((string) $this->input('phone_number', ''));

        $this->merge([
            'email' => $email === '' ? null : strtolower($email),
            'phone_number' => $phone === ''
                ? null
                : (app(UserIdentifierResolver::class)->normalizeLocalPhone($phone) ?? $phone),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required_without:phone_number', 'nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone_number' => ['required_without:email', 'nullable', 'string', 'max:30'],
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:student,parent,guru,bk,staf_tu,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,kepala_sekolah,admin,superadmin',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];
    }
}
