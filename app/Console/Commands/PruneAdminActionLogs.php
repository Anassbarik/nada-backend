<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PruneAdminActionLogs extends Command
{
    protected $signature = 'admin:prune-logs {--days= : Delete logs older than N days (default: config admin_logs.retention_days)}';

    protected $description = 'Prune old admin action logs to keep the table small and fast';

    public function handle(): int
    {
        $days = $this->option('days');
        $days = is_numeric($days) ? (int) $days : (int) config('admin_logs.retention_days', 90);

        if ($days <= 0) {
            $this->error('Days must be a positive integer.');
            return 1;
        }

        $cutoff = Carbon::now()->subDays($days);

        $totalDeleted = 0;
        $batchSize = 2000;

        while (true) {
            $ids = DB::table('admin_action_logs')
                ->where('created_at', '<', $cutoff)
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted = DB::table('admin_action_logs')->whereIn('id', $ids)->delete();
            $totalDeleted += (int) $deleted;

            // Prevent long-running commands from holding locks too long
            usleep(50_000);
        }

        $this->info("Deleted {$totalDeleted} admin action log(s) older than {$days} days.");

        return 0;
    }
}


