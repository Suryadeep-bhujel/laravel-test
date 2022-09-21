<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateCinemaSchema extends Migration
{
    /**
    # Create a migration that creates all tables for the following user stories

    For an example on how a UI for an api using this might look like, please try to book a show at https://in.bookmyshow.com/.
    To not introduce additional complexity, please consider only one cinema.

    Please list the tables that you would create including keys, foreign keys and attributes that are required by the user stories.

    ## User Stories

     **Movie exploration**
     * As a user I want to see which films can be watched and at what times
     * As a user I want to only see the shows which are not booked out

     **Show administration**
     * As a cinema owner I want to run different films at different times
     * As a cinema owner I want to run multiple films at the same time in different locations

     **Pricing**
     * As a cinema owner I want to get paid differently per show
     * As a cinema owner I want to give different seat types a percentage premium, for example 50 % more for vip seat

     **Seating**
     * As a user I want to book a seat
     * As a user I want to book a vip seat/couple seat/super vip/whatever
     * As a user I want to see which seats are still available
     * As a user I want to know where I'm sitting on my ticket
     * As a cinema owner I dont want to configure the seating for every show
     */
    public function up()
    {

        Schema::create('movies', function ($table) {
            $table->id('id');
            $table->string('name');
            $table->boolean('movieStatus')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('locations', function ($table) {
            $table->id('id');
            $table->string('name');
            $table->string('address');
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('screens', function ($table) {
            $table->id('id');
            $table->string('screen');
            $table->unsignedBigInteger("locationId") ;
            $table->timestamps();
            $table->foreign('locationId')->references('id')->on('locations')->onDelete('CASCADE');
            $table->softDeletes();
        });
        Schema::create('seat_types', function ($table) {
            $table->id('id');
            $table->string('name');
            $table->string('color')->nullable(true);
            $table->string('icon')->nullable(true);
            $table->double('percent')->default(0);
            $table->enum('type', ["add", 'subtract'])->default("add");
            $table->unsignedBigInteger("addedby") ->nullable(true);
            $table->timestamps();
            $table->foreign('addedby')->references('id')->on('users');
            $table->softDeletes();
        });

        Schema::create('seat_prices', function ($table) {
            $table->id('id');
            $table->unsignedBigInteger('seat_type_id');
            $table->double("price");
            
            $table->unsignedBigInteger("addedby") ->nullable(true);
            $table->timestamps();
            $table->foreign('addedby')->references('id')->on('users');
            $table->foreign('seat_type_id')->references('id')->on('seat_types');
            $table->softDeletes();
        });



        Schema::create('seats', function ($table) {
            $table->id('id');
            $table->string('seat');

            $table->unsignedBigInteger("screenId")->index();
            $table->unsignedBigInteger("seatTypeId")->index();
            $table->timestamps();
            $table->foreign('screenId')->references('id')->on('screens')->onDelete('CASCADE');
            $table->foreign('seatTypeId')->references('id')->on('seat_types')->onDelete('CASCADE');
            $table->softDeletes();
        });
        Schema::create('showtimes', function ($table) {
            $table->id('id');
            $table->dateTime("startTime");
            $table->dateTime("endTime");
            // $table->dateTime("endTime");
            $table->unsignedBigInteger("addedby")->nullable(true);
            $table->unsignedBigInteger("movieId")->index();
            $table->unsignedBigInteger("locationId")->index();

            $table->timestamps();

            $table->foreign('movieId')->references('id')->on('movies')->onDelete('CASCADE');
            $table->foreign('addedby')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('locationId')->references('id')->on('locations')->onDelete('CASCADE');
            $table->softDeletes();
        });

        Schema::create('selected_seats', function ($table) {
            $table->id('id');
            $table->unsignedBigInteger("movieId")->index();
            $table->unsignedBigInteger("showtimeId")->index();
            $table->unsignedBigInteger("seatId");
            $table->string("orderId", 30);
            $table->unsignedBigInteger("userId")->index();
            $table->enum("type", ['selected', 'reserved', 'unavailable']);
            $table->timestamps();

            $table->foreign('movieId')->references('id')->on('movies')->onDelete('CASCADE');
            $table->foreign('userId')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('showtimeId')->references('id')->on('showtimes')->onDelete('CASCADE');
            $table->foreign('seatId')->references('id')->on('seats')->onDelete('CASCADE');
            
        });

        Schema::create('user_bookings', function ($table) {
            $table->id('id');
            $table->unsignedBigInteger("movieId")->index();
            $table->unsignedBigInteger("showtimeId")->index();
            $table->unsignedBigInteger("seatId");
            $table->string("orderId", 30);
            $table->integer("price");
            $table->unsignedBigInteger("userId")->index();
            $table->timestamps();

            $table->foreign('movieId')->references('id')->on('movies')->onDelete('CASCADE');
            $table->foreign('userId')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('showtimeId')->references('id')->on('showtimes')->onDelete('CASCADE');
            $table->foreign('seatId')->references('id')->on('seats')->onDelete('CASCADE');
            $table->softDeletes();
        });
        // throw new \Exception('implement in coding task 4, you can ignore this exception if you are just running the initial migrations.');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_bookings');
        Schema::dropIfExists('selected_seats');
        Schema::dropIfExists('showtimes');
        Schema::dropIfExists('seats');
        Schema::dropIfExists('seat_prices');
        Schema::dropIfExists('seat_types');
        Schema::dropIfExists('screens');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('movies');

    }
}
