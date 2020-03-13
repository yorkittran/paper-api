<?php

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate([
            'name'     => 'Admin',
            'email'    => 'admin@admin.mail',
            'password' => Hash::make('123456'),
            'role'     => '0',
        ]);
    }
}
