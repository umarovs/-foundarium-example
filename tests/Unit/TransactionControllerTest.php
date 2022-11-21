<?php

namespace Tests\Unit;

use App\Models\Bookings\Transaction;
use App\Models\Transports\Transport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Define checking booking transport with missing parameter
     *
     * @return void
     */
    public function testTransportIsBookedWithMissingParameter(): void
    {
        $faker = (new \Faker\Factory())::create();
        $faker->addProvider(new \Faker\Provider\Fakecar($faker));

        $transport = Transport::create([
            'brand' => $faker->vehicleBrand,
            'name' => $faker->vehicle,
            'vin' => $faker->vin,
            'reg_number' => $faker->vehicleRegistration('[A-Z]{1}-[0-9]{3}-[A-Z]{2}'),
            'description' => $faker->text,
        ]);

        $payload = [
            'transport_id' => $transport->id,
        ];

        $this->json('post', 'api/api-test/transport/booking', $payload)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonStructure(['message']);
    }

    /**
     * Define checking successful booking transport
     *
     * @return void
     */
    public function testTransportIsSuccessfulBooked(): void
    {
        $faker = (new \Faker\Factory())::create();
        $faker->addProvider(new \Faker\Provider\Fakecar($faker));

        $user = User::create([
            'name' => $faker->name,
            'password'  => $faker->password,
            'email'      => $faker->email
        ]);

        $transport = Transport::create([
            'brand' => $faker->vehicleBrand,
            'name' => $faker->vehicle,
            'vin' => $faker->vin,
            'reg_number' => $faker->vehicleRegistration('[A-Z]{1}-[0-9]{3}-[A-Z]{2}'),
            'description' => $faker->text,
        ]);

        $payload = [
            'transport_id' => $transport->id,
            'user_id' => $user->id
        ];

        $this->json('post', 'api/api-test/transport/booking', $payload)
            ->assertNoContent();

        $this->assertDatabaseHas('booking_transactions', $payload);
    }

    /**
     * Define checking un-booking transport with missing parameter
     *
     * @return void
     */
    public function testTransportIsUnBookedWithMissingParameter(): void
    {
        $faker = (new \Faker\Factory())::create();
        $faker->addProvider(new \Faker\Provider\Fakecar($faker));

        $transport = Transport::create([
            'brand' => $faker->vehicleBrand,
            'name' => $faker->vehicle,
            'vin' => $faker->vin,
            'reg_number' => $faker->vehicleRegistration('[A-Z]{1}-[0-9]{3}-[A-Z]{2}'),
            'description' => $faker->text,
        ]);

        $payload = [
            'transport_id' => $transport->id,
        ];

        $this->json('post', 'api/api-test/transport/un-booking', $payload)
            ->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonStructure(['message']);
    }

    /**
     * Define checking successful un-booking transport
     *
     * @return void
     */
    public function testTransportIsSuccessfulUnBooked(): void
    {
        $faker = (new \Faker\Factory())::create();
        $faker->addProvider(new \Faker\Provider\Fakecar($faker));

        $user = User::create([
            'name' => $faker->name,
            'password'  => $faker->password,
            'email'      => $faker->email
        ]);

        $transport = Transport::create([
            'brand' => $faker->vehicleBrand,
            'name' => $faker->vehicle,
            'vin' => $faker->vin,
            'reg_number' => $faker->vehicleRegistration('[A-Z]{1}-[0-9]{3}-[A-Z]{2}'),
            'description' => $faker->text,
            'user_id' => $user->id,
        ]);

        $transaction = Transaction::create([
            'transport_id' => $transport->id,
            'user_id' => $user->id,
            'reserved_from' => now()
        ]);

        $payload = [
            'transport_id' => $transaction->transport_id,
            'user_id' => $transaction->user_id,
        ];

        $this->json('post', 'api/api-test/transport/un-booking', $payload)
            ->assertNoContent();
    }
}
