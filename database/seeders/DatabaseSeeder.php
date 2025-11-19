<?php

namespace Database\Seeders;

use App\Models\User;
<<<<<<< HEAD
=======
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
>>>>>>> 5aa1b22209bd856f792520ff8474479260a2d9d6
use Illuminate\Database\Seeder;

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
            'email' => 'test@example.com',
        ]);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 5aa1b22209bd856f792520ff8474479260a2d9d6
