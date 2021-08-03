<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id');
            $table->enum('type',['sender','receiver','return']);
            $table->string('name',30)->nullable();
            $table->string('lastname',30)->nullable();
            $table->string('email',30)->nullable();
            $table->text('address1')->nullable();
            $table->text('address2')->nullable();
            $table->text('address3')->nullable();
            $table->integer('country_id');
            $table->string('state',20)->nullable();
            $table->string('city',20)->nullable();
            $table->string('postalcode',10)->nullable();
            $table->string('phonenumber',20)->nullable();
            $table->string('company',30)->nullable();
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
        Schema::dropIfExists('booking_addresses');
    }
}
