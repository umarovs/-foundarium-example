<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_id')
                ->nullable()
                ->constrained('transports');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');
            $table->dateTime('reserved_from')->nullable();
            $table->dateTime('reserved_to')->nullable();
            $table->text('geo_point_from')->nullable();
            $table->text('geo_point_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_transactions');
    }
};
