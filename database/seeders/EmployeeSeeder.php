<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    private const EMPLOYEES = [
        [
            'name' => 'جوده',
            'email' => 'joda@pos.local',
        ],
        [
            'name' => 'كريم',
            'email' => 'kareem@pos.local',
        ],
        [
            'name' => 'محمد علي',
            'email' => 'mohammed.ali@pos.local',
        ],
    ];

    /**
     * Seed default employees.
     */
    public function run(): void
    {
        $created = 0;
        $updated = 0;

        foreach (self::EMPLOYEES as $employee) {
            $record = Employee::updateOrCreate(
                ['email' => $employee['email']],
                [
                    'name' => $employee['name'],
                    'password' => 'password',
                    'role' => 'cashier',
                    'phone' => null,
                    'salary' => 0,
                    'hiring_date' => now()->toDateString(),
                    'active' => true,
                ],
            );

            $record->wasRecentlyCreated ? $created++ : $updated++;
        }

        $this->command?->info("Employees seeded: {$created} created, {$updated} updated.");
    }
}
