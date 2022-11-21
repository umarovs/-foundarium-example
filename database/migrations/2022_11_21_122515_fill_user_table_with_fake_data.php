<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $faker = (new \Faker\Factory())::create();
        foreach (range(1, 10) as $i) {
            \Illuminate\Support\Facades\DB::table("users")
                ->insert([
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'password' => bcrypt($faker->password)
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
