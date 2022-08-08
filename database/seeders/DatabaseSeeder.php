<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory()->create([
            'name' => 'Adriano',
            'email' => 'adriano@email.com',
        ]);
        \App\Models\User::factory()->create([
            'name' => 'Rodrigo',
            'email' => 'rodrigo@email.com',
        ]);

        \App\Models\Team::factory()->create([
            'name' => "Skill Shortage",
            'tag' => "SSA",
            'user_id' => 1
        ]);

        \App\Models\Team::factory()->create([
            'name' => "MTR Gaming",
            'tag' => "MTR",
            'user_id' => 2
        ]);

        \App\Models\User::factory(10)->create();
    }
}
