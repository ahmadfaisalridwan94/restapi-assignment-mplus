<?php

namespace Database\Seeders;

use App\Helpers\StringHelper;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            'id' => Str::uuid(),
            'name' => 'Administrator',
            'username' => StringHelper::generateUniqueUsername('Administrator'),
            'email' => 'admin@example.com',
            'password' => bcrypt('S3cretsekali'),
            'role' => 'admin'
        ]);
    }
}
