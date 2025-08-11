<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $students = User::where('role', Role::STUDENT)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('other_name', 'like', "%{$search}%")
                        ->orWhere('lin', 'like', "%{$search}%");
                });
            })
            ->when($request->stream_id, function ($query, $stream_id) {
                $query->whereHas('streams', function ($q) use ($stream_id) {
                    $q->where('streams.id', $stream_id);
                });
            })
            ->orderBy('last_name')
            ->paginate(20);

        $streams = Stream::with('classLevel')->get();

        return view('students.index', compact('students', 'streams'));
    }

    public function updatePhoto(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'photo_upload' => ['nullable', 'image', 'max:2048'],
            'photo_data' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('photo_upload')) {
            $path = $request->file('photo_upload')->store('photos', 'public');
            $user->update(['photo' => $path]);
        } elseif ($request->photo_data) {
            $img = $request->photo_data;
            $img = str_replace('data:image/jpeg;base64,', '', $img);
            $img = str_replace(' ', '+', $img);
            $data = base64_decode($img);
            $filename = 'photos/' . uniqid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $data);
            $user->update(['photo' => $filename]);
        }

        return redirect()->route('students.index')->with('success', 'Photo updated successfully.');
    }

    public function showUploadForm(): View
    {
        return view('students.upload');
    }

    public function import(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\StudentsImport, $request->file('file'));

        return redirect()->route('students.index')->with('success', 'Students imported successfully.');
    }

    public function exportExcel(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StudentsExport($request->stream_id), 'students.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $studentsQuery = User::where('role', \App\Enums\Role::STUDENT)
            ->when($request->stream_id, function ($query, $stream_id) {
                $query->whereHas('streams', function ($q) use ($stream_id) {
                    $q->where('streams.id', $stream_id);
                });
            })
            ->orderBy('last_name');

        $students = $studentsQuery->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.pdf', compact('students'));
        return $pdf->download('students.pdf');
    }

    public function generateReportCard(User $user, Stream $stream)
    {
        // Get all subjects for the stream
        $subjects = $stream->subjects()->with('papers')->get();

        // Get all marks for the student in this stream
        $marks = \App\Models\Mark::where('user_id', $user->id)
            ->where('stream_id', $stream->id)
            ->pluck('score', 'paper_id');

        // This is a placeholder for a real grading system
        $gradingSystem = [
            ['min' => 80, 'max' => 100, 'grade' => 'D1'],
            ['min' => 75, 'max' => 79, 'grade' => 'D2'],
            // ... and so on
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.report-card', [
            'student' => $user,
            'stream' => $stream,
            'subjects' => $subjects,
            'marks' => $marks,
            'gradingSystem' => $gradingSystem,
        ]);

        return $pdf->stream('report-card.pdf');
    }

    public function generateIdCard(User $user)
    {
        $issue_date = now()->format('d-m-Y');
        $expiry_date = now()->addYears(1)->format('d-m-Y');

        // A QR code should point to a public-facing, stable URL.
        // We'll assume a simple profile 'show' route exists for this.
        $qrCode = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(50)->generate(route('users.show', $user)));

        $photoData = null;
        try {
            if ($user->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo)) {
                $photoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($user->photo);
                $type = pathinfo($photoPath, PATHINFO_EXTENSION);
                $photoData = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($photoPath));
            } else {
                // Fallback to UI Avatars
                $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF&size=128';
                $imageData = file_get_contents($avatarUrl);
                $photoData = 'data:image/png;base64,' . base64_encode($imageData);
            }
        } catch (\Exception $e) {
            // Log error or handle gracefully
            // For now, we'll leave photoData as null if everything fails
        }


        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.id-card', [
            'student' => $user,
            'issue_date' => $issue_date,
            'expiry_date' => $expiry_date,
            'qrCode' => $qrCode,
            'photoData' => $photoData,
        ]);

        return $pdf->stream('id-card.pdf');
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new class implements \Maatwebsite\Excel\Concerns\WithHeadings {
            public function headings(): array
            {
                return [
                    'lin',
                    'first_name',
                    'last_name',
                    'other_name',
                    'date_of_birth (YYYY-MM-DD)',
                    'email',
                    'gender',
                ];
            }
        }, 'students_template.xlsx');
    }

    public function search(Request $request)
    {
        $query = $request->get('query');
        $students = User::where('role', \App\Enums\Role::STUDENT)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('lin', 'like', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($students);
    }
}
