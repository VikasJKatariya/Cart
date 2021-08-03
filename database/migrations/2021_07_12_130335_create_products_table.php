<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku',100)->nullable()->default('NULL');
            $table->string('parent_id',100)->nullable()->default('NULL');
            $table->string('title',100)->nullable()->default('NULL');
            $table->integer('quantity')->unsigned();
            $table->decimal('selling_price', 10,2)->nullable();
            $table->decimal('buying_price', 10,2)->nullable();
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
        Schema::dropIfExists('products');
    }
}
