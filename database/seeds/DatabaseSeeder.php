<?php

use App\Models\Group;
use App\Models\User;
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
        $this->call(UserSeeder::class);

        // Create 5 managers and group belongs to
        factory(User::class, 5)->create()->each(function($user) {
            factory(Group::class, 1)->create([
                'manager_id' => $user->id,
            ]);
        });

        // Create 50 users and belongs to group
        // factory(User::class, 50)->create()->each(function($user) {
        //     $user->group_id = rand(1, 5);
        //     $user->update();
        // });
    }
}
