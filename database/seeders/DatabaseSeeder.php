<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run essential seeders for production
        $this->call([
            UserSeeder::class,        // Creates 3 test users (admin, teacher, student)
            SettingsSeeder::class,    // Creates basic system settings
        ]);
        
        // Test data seeders - COMMENTED OUT FOR PRODUCTION
        // Uncomment these if you want test courses and data
        // $this->call([
        //     CourseTestDataSeeder::class,
        //     MessageSeeder::class,
        //     TestEnrollmentSeeder::class,
        // ]);
    }
}
