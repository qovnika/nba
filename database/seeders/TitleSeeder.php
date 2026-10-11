<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Title::factory()->create([
            'title' => "Barrister",
            'description' => "Lawyer of the Nigerian Bar Association",
            'due' => 5000
        ]);
        \App\Models\Title::factory()->create([
            'title' => "Senior Advocate of Nigeria (SAN)",
            'description' => "Senior Advocate of Nigeria (SAN)",
            'due' => 15000
        ]);
    }
}
