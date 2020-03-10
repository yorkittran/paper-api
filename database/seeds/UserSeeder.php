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
        User::create(array(
            'name'     => 'Yorkit Tran',
            'email'    => 'yorkittran@gmail.com',
            'password' => Hash::make('haha1234'),
            'role'     => '0',
        ));
    }
}
