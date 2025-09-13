<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:super-admin 
                            {email=admin@example.com : The admin email address}
                            {password=password : The admin password}
                            {name=Super Admin : The admin name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update a super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->argument('name');

        // Check if user already exists
        $user = User::where('email', $email)->first();

        if ($user) {
            // Update existing user to super admin
            $user->update([
                'name' => $name,
                'role' => 'super_admin',
                'password' => Hash::make($password),
                'is_active' => true,
            ]);
            
            $this->info("✅ Super admin user updated successfully!");
            $this->info("Email: {$email}");
            $this->info("Password: {$password}");
            $this->info("Role: super_admin");
        } else {
            // Create new super admin user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
            ]);
            
            $this->info("✅ Super admin user created successfully!");
            $this->info("Email: {$email}");
            $this->info("Password: {$password}");
            $this->info("Role: super_admin");
        }

        $this->info("\n🔗 You can now login at: http://127.0.0.1:8000/admin/dashboard");
        
        return 0;
    }
}
