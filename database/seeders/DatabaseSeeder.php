<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'session_id' => Str::uuid(),
            'email' => 'test@app.com',
            'password' => Hash::make('12345678')
        ]);
        User::factory()->create([
            'name' => 'admin User',
            'session_id' => Str::uuid(),
            'email' => 'admin@app.com',
            'password' => Hash::make('12345678')
        ]);
        User::factory()->create([
            'name' => 'user User',
            'session_id' => Str::uuid(),
            'email' => 'user@app.com',
            'password' => Hash::make('12345678')
        ]);
    }
}
