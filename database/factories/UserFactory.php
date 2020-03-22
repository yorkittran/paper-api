<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    // return [
    //     'name'     => 'Manager ' . $faker->unique()->name,
    //     'email'    => 'manager-' . $faker->unique()->randomDigitNotNull . '@manager.mail',
    //     'password' => Hash::make('123456'),
    //     'role'     => '1',
    // ];
    return [
        'name'     => 'User ' . $faker->name,
        'email'    => 'user-' . $faker->unique()->numberBetween(1, 50) . '@user.mail',
        'password' => Hash::make('123456'),
        'role'     => '2',
    ];
});
