<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HealthRecordController extends Controller
{
    /**
     * Show the form for editing the specified student's health record.
     */
    public function edit(User $student): View
    {
        if ($student->role !== \App\Enums\Role::STUDENT) {
            abort(404, 'User is not a student.');
        }

        // Find the health record or create a new instance if it doesn't exist
        $healthRecord = HealthRecord::firstOrNew(['user_id' => $student->id]);

        return view('students.health_record_edit', compact('student', 'healthRecord'));
    }

    /**
     * Update the specified student's health record in storage.
     */
    public function update(Request $request, User $student): RedirectResponse
    {
        if ($student->role !== \App\Enums\Role::STUDENT) {
            abort(404, 'User is not a student.');
        }

        $request->validate([
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $student->healthRecord()->updateOrCreate(
            ['user_id' => $student->id],
            $request->all()
        );

        return redirect()->route('students.show', $student)
            ->with('success', 'Health record updated successfully.');
    }
}
