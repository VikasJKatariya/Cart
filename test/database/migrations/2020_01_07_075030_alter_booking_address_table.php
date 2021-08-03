<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBookingAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booking_addresses', function (Blueprint $table) {
            $table->enum('id_type',['Driving License','Social Security number', 'Aadhar card', 'Pan card'])->after('company')->nullable();
            $table->string('id_number')->after('id_type')->nullable();
            $table->string('id_doc_image')->after('id_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking_addresses', function (Blueprint $table) {
            //
        });
    }
}
