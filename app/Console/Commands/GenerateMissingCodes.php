<?php

namespace App\Console\Commands;

use App\Models\Logbook;
use App\Models\User;
use App\Services\UniqueCodeGenerator;
use Illuminate\Console\Command;

class GenerateMissingCodes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'codes:generate-missing
                            {--type=all : Which codes to generate: all, users, logbooks}
                            {--dry-run  : Show what would be generated without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Generate user_code and logbook_code for existing records that do not have one.';

    public function handle(UniqueCodeGenerator $generator): int
    {
        $type   = $this->option('type');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no data will be saved.');
        }

        if (in_array($type, ['all', 'users'])) {
            $this->generateForUsers($generator, $dryRun);
        }

        if (in_array($type, ['all', 'logbooks'])) {
            $this->generateForLogbooks($generator, $dryRun);
        }

        $this->info('Done.');
        return self::SUCCESS;
    }

    private function generateForUsers(UniqueCodeGenerator $generator, bool $dryRun): void
    {
        $users = User::whereNull('user_code')->get();

        if ($users->isEmpty()) {
            $this->line('Users: No records need user_code generation.');
            return;
        }

        $this->info("Users: Found {$users->count()} record(s) without user_code.");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $errors = 0;
        foreach ($users as $user) {
            try {
                $code = $generator->generateUserCode($user->id);
                if (! $dryRun) {
                    $user->updateQuietly(['user_code' => $code]);
                } else {
                    $this->newLine();
                    $this->line("  [DRY] user #{$user->id} → {$code}");
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error("  Failed for user #{$user->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Users: Done. Errors: {$errors}");
    }

    private function generateForLogbooks(UniqueCodeGenerator $generator, bool $dryRun): void
    {
        $logbooks = Logbook::whereNull('logbook_code')->get();

        if ($logbooks->isEmpty()) {
            $this->line('Logbooks: No records need logbook_code generation.');
            return;
        }

        $this->info("Logbooks: Found {$logbooks->count()} record(s) without logbook_code.");
        $bar = $this->output->createProgressBar($logbooks->count());
        $bar->start();

        $errors = 0;
        foreach ($logbooks as $logbook) {
            try {
                $code = $generator->generateLogbookCode();
                if (! $dryRun) {
                    $logbook->updateQuietly(['logbook_code' => $code]);
                } else {
                    $this->newLine();
                    $this->line("  [DRY] logbook #{$logbook->id} → {$code}");
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->newLine();
                $this->error("  Failed for logbook #{$logbook->id}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Logbooks: Done. Errors: {$errors}");
    }
}
