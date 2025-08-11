<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserActionNotification;
use App\Enums\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::orderBy('first_name')->orderBy('last_name')->paginate(20);

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        $classLevels = \App\Models\ClassLevel::orderBy('name')->get();
        $streams = \App\Models\Stream::with('classLevel')->get();
        return view('users.create', compact('classLevels', 'streams'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string', new \Illuminate\Validation\Rules\Enum(\App\Enums\Role::class)],
            'lin' => ['nullable', 'string', 'max:255', 'unique:users,lin'],
            'stream_id' => ['nullable', 'exists:streams,id'],
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'role' => $request->role,
            'lin' => $request->lin,
            'password' => \Illuminate\Support\Facades\Hash::make('password'), // Default password
        ]);

        if ($request->role === 'student' && $request->stream_id) {
            $user->streams()->attach($request->stream_id);
        }

        // Notify admins
        $admins = User::whereIn('role', [Role::ROOT, Role::HEADTEACHER])->get();
        $message = "A new user '{$user->name}' of type '{$user->role->value}' was created.";
        Notification::send($admins, new UserActionNotification($message, route('users.edit', $user)));


        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $classLevels = \App\Models\ClassLevel::orderBy('name')->get();
        $streams = \App\Models\Stream::with('classLevel')->get();
        // Get the student's current stream if it exists
        $currentStream = $user->streams()->first();
        return view('users.edit', compact('user', 'classLevels', 'streams', 'currentStream'));
    }

    public function update(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', new \Illuminate\Validation\Rules\Enum(\App\Enums\Role::class)],
            'lin' => ['nullable', 'string', 'max:255', 'unique:users,lin,'.$user->id],
            'stream_id' => ['nullable', 'exists:streams,id'],
        ]);

        $user->update($request->all());

        if ($request->role === 'student' && $request->stream_id) {
            $user->streams()->sync([$request->stream_id]);
        } elseif ($request->role !== 'student') {
            // If user is no longer a student, remove all stream assignments
            $user->streams()->sync([]);
        }


        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }
}
