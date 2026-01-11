<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RoleSeeder::class);
        $this->call(LocationSeeder::class);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'password' => bcrypt('password'),
            'email' => 'a@a',
        ]);

        $user = User::factory()->create([
            'name' => 'User',
            'email' => 'u@u',
        ]);

        $admin->assignRole('admin');
        $user->assignRole('user');
    }
}
