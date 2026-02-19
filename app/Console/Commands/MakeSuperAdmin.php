<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetrix:make-admin {email}';
    protected $description = 'Grant Super Admin privileges to a user';

    public function handle()
    {
        $email = $this->argument('email');
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->update(['is_super_admin' => true]);
        $this->info("User {$user->name} ({$email}) is now a Super Admin.");
        return 0;
    }
}
