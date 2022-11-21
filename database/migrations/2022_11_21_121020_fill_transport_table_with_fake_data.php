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
        $faker->addProvider(new \Faker\Provider\Fakecar($faker));

        foreach (range(1, 10) as $i) {
            \Illuminate\Support\Facades\DB::table("transports")
                ->insert([
                    'brand' => $faker->vehicleBrand,
                    'name' => $faker->vehicle,
                    'vin' => $faker->vin,
                    'reg_number' => $faker->vehicleRegistration('[A-Z]{1}-[0-9]{3}-[A-Z]{2}'),
                    'description' => $faker->text,
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
        //
    }
};
