<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_wallet_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('status',['add','reduce'])->default('add');
            $table->decimal('changed_amount', 10,2)->default(0.00);
            $table->decimal('current_amount', 10,2)->default(0.00);
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
        Schema::dropIfExists('agent_wallet_logs');
    }
}
