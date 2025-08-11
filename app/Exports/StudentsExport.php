<?php

namespace App\Exports;

use App\Enums\Role;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $streamId;

    public function __construct($streamId = null)
    {
        $this->streamId = $streamId;
    }

    public function query()
    {
        return User::query()
            ->where('role', Role::STUDENT)
            ->when($this->streamId, function ($query, $streamId) {
                $query->whereHas('streams', function ($q) use ($streamId) {
                    $q->where('streams.id', $streamId);
                });
            })
            ->with('streams.classLevel'); // Eager load for performance
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'LIN',
            'First Name',
            'Last Name',
            'Other Name',
            'Date of Birth',
            'Email',
            'Gender',
            'Stream',
        ];
    }

    public function map($student): array
    {
        return [
            $student->unique_id,
            $student->lin,
            $student->first_name,
            $student->last_name,
            $student->other_name,
            $student->date_of_birth,
            $student->email,
            $student->gender,
            $student->streams->map(function ($stream) {
                return $stream->classLevel->name . ' ' . $stream->name;
            })->implode(', '),
        ];
    }
}
