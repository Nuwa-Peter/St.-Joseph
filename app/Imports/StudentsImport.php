<?php

namespace App\Imports;

use App\Enums\Role;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // The key is sanitized from 'date_of_birth (YYYY-MM-DD)'
        $dobKey = 'date_of_birth_yyyy_mm_dd';

        return new User([
            'lin' => $row['lin'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'other_name' => $row['other_name'],
            'date_of_birth' => isset($row[$dobKey]) ? \Carbon\Carbon::parse($row[$dobKey])->format('Y-m-d') : null,
            'email' => $row['email'],
            'gender' => $row['gender'],
            'password' => \Illuminate\Support\Facades\Hash::make('password'), // Default password
            'role' => Role::STUDENT,
        ]);
    }
}
