<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'AdminIT',
            'email' => 'adminit@gmail.com',
            'password' => bcrypt('adminit')
        ]);

        $role = Role::where('name', 'Admin')->first();

        if ($role) {
            $user->assignRole([$role->id]);
        }
    }
}