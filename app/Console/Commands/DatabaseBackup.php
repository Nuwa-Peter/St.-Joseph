<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $backupPath = storage_path('app/backups');
        File::ensureDirectoryExists($backupPath);

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');
        $dbPort = config('database.connections.mysql.port');

        $fileName = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sql.gz';
        $filePath = $backupPath . '/' . $fileName;

        // The command to dump and gzip the database
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s %s | gzip > %s',
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );

        $process = Process::fromShellCommandline($command);

        // Set a reasonable timeout
        $process->setTimeout(3600);

        try {
            $process->mustRun();
            $this->info('Database backup successful!');
            $this->info("Backup stored at: {$filePath}");
        } catch (ProcessFailedException $exception) {
            $this->error('Database backup failed.');
            $this->error($exception->getMessage());
        }
    }
}
