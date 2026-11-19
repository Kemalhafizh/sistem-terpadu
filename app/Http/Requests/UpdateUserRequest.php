<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Services\Auth\UserIdentifierResolver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    private function resolveTargetUser(): ?User
    {
        $param = $this->route('user');
        if ($param instanceof User) {
            return $param;
        }

        return is_numeric($param)
            ? User::find((int) $param)
            : User::where('uuid', $param)->first();
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $actor = $this->user();
        $target = $this->resolveTargetUser();

        if ($target?->role === 'superadmin' && $actor?->role !== 'superadmin') {
            return false;
        }

        if ($this->input('role') === 'superadmin' && $actor?->role !== 'superadmin') {
            return false;
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
        $userId = $this->resolveTargetUser()?->id ?? $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => ['required_without:phone_number', 'nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone_number' => ['required_without:email', 'nullable', 'string', 'max:30'],
            'role' => 'required|string|in:student,parent,guru,bk,staf_tu,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,kepala_sekolah,admin,superadmin',
            'password' => 'nullable|string|min:8',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];
    }
}
