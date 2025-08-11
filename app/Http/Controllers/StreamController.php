<?php

namespace App\Http\Controllers;

use App\Models\ClassLevel;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class StreamController extends Controller
{
    public function index(ClassLevel $classLevel): View
    {
        return view('streams.index', [
            'classLevel' => $classLevel,
            'streams' => $classLevel->streams()->orderBy('name')->get(),
        ]);
    }

    public function create(ClassLevel $classLevel): View
    {
        return view('streams.create', compact('classLevel'));
    }

    public function store(Request $request, ClassLevel $classLevel): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:streams,name,NULL,id,class_level_id,'.$classLevel->id],
        ]);

        $classLevel->streams()->create($request->all());

        return redirect()->route('class-levels.streams.index', $classLevel)->with('success', 'Stream created successfully.');
    }

    public function edit(Stream $stream): View
    {
        return view('streams.edit', compact('stream'));
    }

    public function update(Request $request, Stream $stream): RedirectResponse
    {
        $classLevel = $stream->classLevel;
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:streams,name,'.$stream->id.',id,class_level_id,'.$classLevel->id],
        ]);

        $stream->update($request->all());

        return redirect()->route('class-levels.streams.index', $classLevel)->with('success', 'Stream updated successfully.');
    }

    public function destroy(Stream $stream): RedirectResponse
    {
        $classLevel = $stream->classLevel;
        $stream->delete();

        return redirect()->route('class-levels.streams.index', $classLevel)->with('success', 'Stream deleted successfully.');
    }
}
