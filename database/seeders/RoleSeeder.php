<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Has full access to the entire system.',
            ],
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
                'description' => 'Has administrative access to the system.',
            ],
            [
                'name' => 'Apostille Officer',
                'slug' => 'apostille-officer',
                'description' => 'Can manage services and ligal content.',
            ],
            [
                'name' => 'Customer Support',
                'slug' => 'customer-support',
                'description' => 'Can manage user and associated customer support tickets.',
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Can manage financial transactions and reports.',
            ],
            [
                'name' => 'Courier',
                'slug' => 'courier',
                'description' => 'Can manage delivery and logistics.',
            ],
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Can access and use the services provided by the system as a single user.',
            ],
            [
                'name' => 'Business Client',
                'slug' => 'business-client',
                'description' => 'Can access and use the services provided by the system for business purposes.',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}