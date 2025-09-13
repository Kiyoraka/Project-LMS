<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LoginAttempt;

class ClearExpiredLockouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:clear-expired-lockouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear expired login attempt lockouts from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing expired login lockouts...');
        
        $cleared = LoginAttempt::where('locked_until', '<', now())->count();
        LoginAttempt::clearExpiredLockouts();
        
        $this->info("Cleared {$cleared} expired lockouts (both login and registration attempts).");
        
        return 0;
    }
}
