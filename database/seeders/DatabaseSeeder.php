<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Panggil kunci masternya di sini
        $this->call([
            AdminSeeder::class,
        ]);
    }
}
