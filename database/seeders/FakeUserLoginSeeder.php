<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FakeUserLoginSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Morocco Demo Admin',
                'email' => 'maroc.demo@landingbuilder.local',
                'password' => 'password123',
            ],
            [
                'name' => 'Demo Admin',
                'email' => 'admin.demo@landingbuilder.local',
                'password' => 'password123',
            ],
            [
                'name' => 'Demo Marketer',
                'email' => 'marketer.demo@landingbuilder.local',
                'password' => 'password123',
            ],
            [
                'name' => 'Demo Support',
                'email' => 'support.demo@landingbuilder.local',
                'password' => 'password123',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make($user['password']),
                    'email_verified_at' => now(),
                ]
            );
        }

        $this->command?->info('Fake login users seeded. Password for all demo users: password123');
    }
}
