<?php

namespace App\Observers;

use App\Enums\Role;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        // The role will be a string from the request, or an enum if set manually.
        // Let's handle both cases.
        $roleValue = $user->role instanceof Role ? $user->role->value : $user->role;

        if ($roleValue === Role::STUDENT->value || $roleValue === Role::TEACHER->value) {
            $prefix = 'STJ';
            $genderCode = strtoupper(substr($user->gender, 0, 1)); // M or F
            $idPrefix = $prefix . $genderCode;

            $isStudent = $roleValue === Role::STUDENT->value;
            $numberLength = $isStudent ? 4 : 3;

            // Find the last user with a similar ID to determine the next number
            $latestUser = User::where('role', $user->role)
                              ->where('gender', $user->gender)
                              ->orderBy('id', 'desc')
                              ->first();

            $nextNumber = 1;
            if ($latestUser && $latestUser->unique_id) {
                // A more robust way is to get the latest ID for that role/gender and increment it
                $numericPart = (int) substr($latestUser->unique_id, strlen($idPrefix));
                $nextNumber = $numericPart + 1;
            }

            $user->unique_id = $idPrefix . str_pad($nextNumber, $numberLength, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
