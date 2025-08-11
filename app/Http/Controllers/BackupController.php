<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root,headteacher');
    }

    /**
     * Display a listing of the backups.
     */
    public function index(): View
    {
        $backupFiles = Storage::disk('local')->files('backups');

        $backups = collect($backupFiles)->map(function ($file) {
            return [
                'name' => basename($file),
                'size' => $this->formatBytes(Storage::disk('local')->size($file)),
                'last_modified' => date('Y-m-d H:i:s', Storage::disk('local')->lastModified($file)),
            ];
        })->sortByDesc('last_modified')->values();

        return view('advanced.backups.index', compact('backups'));
    }

    /**
     * Trigger a new database backup.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            Artisan::call('db:backup');
            return redirect()->route('backups.index')->with('success', 'New database backup created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('backups.index')->with('error', 'Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Download a specific backup file.
     */
    public function download(string $fileName): StreamedResponse
    {
        $filePath = 'backups/' . $fileName;

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404);
        }

        return Storage::disk('local')->download($filePath);
    }

    /**
     * Delete a specific backup file.
     */
    public function destroy(string $fileName): RedirectResponse
    {
        $filePath = 'backups/' . $fileName;

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404);
        }

        Storage::disk('local')->delete($filePath);

        return redirect()->route('backups.index')->with('success', 'Backup file deleted successfully.');
    }

    /**
     * Format bytes into a human-readable string.
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
