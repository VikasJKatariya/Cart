<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceSlabsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_slabs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('zones_id');
            $table->integer('weight_id');
            $table->decimal('upx_price',10,2);
            $table->decimal('agent_price',10,2);
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
        Schema::dropIfExists('price_slabs');
    }
}
