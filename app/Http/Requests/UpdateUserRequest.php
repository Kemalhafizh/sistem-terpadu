<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $actor = $this->user();
        $target = User::find($this->route('user'));

        if ($target?->role === 'superadmin' && $actor?->role !== 'superadmin') {
            return false;
        }

        if ($this->input('role') === 'superadmin' && $actor?->role !== 'superadmin') {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$userId,
            'role' => 'required|string|in:student,parent,guru,bk,staf_tu,waka_kurikulum,waka_kesiswaan,waka_sarpras,waka_humas,kepala_sekolah,admin,superadmin',
            'password' => 'nullable|string|min:8',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:subjects,id',
        ];
    }
}
