<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::query()->updateOrCreate(
            ['email' => 'admin@pos.local'],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'password' => 'password',
            ],
        );

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            MenuProductSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}
