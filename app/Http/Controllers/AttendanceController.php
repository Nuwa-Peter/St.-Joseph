<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendanceController extends Controller
{
    public function showQrCode(): View
    {
        // The QR code will contain a URL to the scan route, with a timestamp to make it unique
        $qrCode = QrCode::size(300)->generate(route('attendance.scan', ['timestamp' => now()->timestamp]));
        return view('attendance.qrcode', compact('qrCode'));
    }

    public function scan(Request $request): RedirectResponse
    {
        $teacher = Auth::user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $teacher->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            // Already checked in, so this is a check-out
            if (is_null($attendance->check_out)) {
                $attendance->update(['check_out' => now()]);
                return redirect()->route('dashboard')->with('status', 'Successfully checked out.');
            } else {
                return redirect()->route('dashboard')->with('status', 'You have already checked out for today.');
            }
        } else {
            // This is a check-in
            Attendance::create([
                'user_id' => $teacher->id,
                'check_in' => now(),
                'date' => $today,
            ]);
            return redirect()->route('dashboard')->with('status', 'Successfully checked in.');
        }
    }

    public function records(Request $request): View
    {
        $attendances = Attendance::with('user')
            ->when($request->date, fn($q, $date) => $q->where('date', $date))
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->paginate(30);

        return view('attendance.records', compact('attendances'));
    }
}
