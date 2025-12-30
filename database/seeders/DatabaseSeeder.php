<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\RhtCongeAgentSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            "password" => bcrypt('password'), // mot de passe par défaut
        ]);

        // Import RhtCongeAgent data if SQL file is present
        $this->call(RhtCongeAgentSeeder::class);
    }
}
