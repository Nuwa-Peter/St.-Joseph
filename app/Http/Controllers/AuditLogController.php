<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $logs = AuditLog::with('user', 'auditable')
            ->latest()
            ->paginate(50);

        return view('admin.audit.index', compact('logs'));
    }
}
