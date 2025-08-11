<?php

namespace App\Http\Controllers;

use App\Models\Stream;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class VideoController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        if ($user->role->value === 'student') {
            $streams = $user->streams()->pluck('id');
            $videos = Video::whereHas('streams', fn($q) => $q->whereIn('streams.id', $streams))->get();
        } else {
            // Teacher
            $videos = $user->videos()->get();
        }

        return view('videos.index', compact('videos'));
    }

    public function create(): View
    {
        $streams = Stream::with('classLevel')->get();
        return view('videos.create', compact('streams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video' => ['required', 'file', 'mimes:mp4,mov,ogg,qt', 'max:20000'], // 20MB Max
            'streams' => ['required', 'array', 'min:1'],
            'streams.*' => ['exists:streams,id'],
        ]);

        $path = $request->file('video')->store('videos', 'public');

        $video = Auth::user()->videos()->create([
            'title' => $request->title,
            'description' => $request->description,
            'path' => $path,
        ]);

        $video->streams()->sync($request->streams);

        return redirect()->route('videos.index')->with('success', 'Video uploaded successfully.');
    }
}
