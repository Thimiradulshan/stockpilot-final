<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        if (config('stockpilot.seed_demo')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
