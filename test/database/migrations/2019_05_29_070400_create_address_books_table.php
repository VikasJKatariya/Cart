<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',30);
            $table->string('email',30);
            $table->string('phonenumber',30);
            $table->string('company',30);
            $table->integer('country_id');
            $table->string('state',30);
            $table->string('city',30);
            $table->text('address1');
            $table->text('address2');
            $table->text('address3');
            $table->string('postalcode',30);
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
        Schema::dropIfExists('address_books');
    }
}
