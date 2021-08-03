<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booked_by');
            $table->enum('current_status',['shipped','delivered'])->default('shipped');
            $table->string('package_type');
            $table->string('tracking_number');
            $table->text('discription');
            $table->text('booking_instruction');
            $table->decimal('upx_price', 5,2);
            $table->decimal('agent_price', 5,2);
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
        Schema::dropIfExists('bookings');
    }
}
