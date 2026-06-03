<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        $user = User::create([
            'name' => $row['nama'],
            'email' => $row['email'],
            'password' => Hash::make($row['nis']),
            'role' => 'student',
            'must_change_password' => true,
        ]);

        return new Student([
            'user_id' => $user->id,
            'nis' => $row['nis'],
            'gender' => $row['gender'] ?? 'L',
            'classroom_id' => $row['classroom_id'],
            'status' => 'active',
        ]);
    }
}
