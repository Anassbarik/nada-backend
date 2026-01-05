<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdatePackageDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packages:update-dates 
                            {--month=2 : Target month (default: 2 for February)}
                            {--year=2026 : Target year (default: 2026)}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all package check_in/check_out dates from January to February 2026, preserving duration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetMonth = (int) $this->option('month');
        $targetYear = (int) $this->option('year');
        $dryRun = $this->option('dry-run');

        $this->info("Updating packages from January to {$targetYear}-{$targetMonth}...");
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Get all packages with January check_in dates
        $packages = Package::whereMonth('check_in', 1)
            ->whereYear('check_in', 2026)
            ->get();

        if ($packages->isEmpty()) {
            $this->info('No packages found with January 2026 dates.');
            return Command::SUCCESS;
        }

        $this->info("Found {$packages->count()} packages to update.");

        $updated = 0;
        $bar = $this->output->createProgressBar($packages->count());
        $bar->start();

        foreach ($packages as $package) {
            // Calculate duration in days
            $duration = $package->check_in->diffInDays($package->check_out);
            
            // Get the day of month from original check_in
            $dayOfMonth = $package->check_in->day;
            
            // Create new check_in date in target month/year
            $newCheckIn = Carbon::create($targetYear, $targetMonth, $dayOfMonth);
            
            // Calculate new check_out date (preserving duration)
            $newCheckOut = $newCheckIn->copy()->addDays($duration);

            if (!$dryRun) {
                $package->check_in = $newCheckIn;
                $package->check_out = $newCheckOut;
                $package->save();
            }

            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("Would update {$updated} packages.");
            $this->warn('Run without --dry-run to apply changes.');
        } else {
            $this->info("Successfully updated {$updated} packages!");
            $this->info("All dates moved from January to {$targetYear}-{$targetMonth}.");
        }

        return Command::SUCCESS;
    }
}
