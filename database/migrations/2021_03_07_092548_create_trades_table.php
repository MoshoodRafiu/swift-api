<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id');
            $table->foreignId('seller_id');
            $table->foreignId('agent_id')->nullable();
            $table->foreignId('advert_id');
            $table->double('amount');
            $table->double('amount_usd');
            $table->double('amount_ngn');
            $table->boolean('buyer_has_summoned')->default(false);
            $table->boolean('seller_has_summoned')->default(false);
            $table->integer('buyer_status')->default(0);
            $table->integer('seller_status')->default(0);
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('trades');
    }
}
