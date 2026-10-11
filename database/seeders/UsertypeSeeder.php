<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsertypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Usertype::factory()->create([
            'name' => "Lawyer",
            'description' => "Lawyer of the Nigerian Bar Association"
        ]);
        \App\Models\Usertype::factory()->create([
            'name' => "Administrator",
            'description' => "System Administrator"
        ]);
        \App\Models\Usertype::factory()->create([
            'name' => "Super Administrator",
            'description' => "System Super administrator"
        ]);
    }
}
