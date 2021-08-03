<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookingDimensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking_dimensions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id');
            $table->decimal('lenth', 5,2)->default(0.00);
            $table->decimal('width', 5,2)->default(0.00);;
            $table->decimal('height', 5,2)->default(0.00);;
            $table->decimal('weight', 5,2)->default(0.00);;
            $table->decimal('insure_amt', 5,2)->default(0.00);
            $table->decimal('total_on_dimension', 5,2)->default(0.00);
            $table->decimal('total_on_weight', 5,2)->default(0.00);
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
        Schema::dropIfExists('booking_dimensions');
    }
}

