<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku_id',100)->nullable()->default('NULL');
            $table->string('order_id')->nullable()->default('NULL');
            $table->integer('item_quantity')->unsigned();
            $table->decimal('item_price', 10,2)->nullable();
            $table->date('order_date');
            $table->decimal('item_profit', 10,2)->nullable();
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
        Schema::dropIfExists('orders');
    }
}
