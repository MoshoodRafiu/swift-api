<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->boolean('email')->default(false);
            $table->boolean('phone')->default(false);
            $table->boolean('kyc')->default(false);
            $table->timestamp('email_ver_at')->nullable();
            $table->timestamp('phone_ver_at')->nullable();
            $table->timestamp('kyc_ver_at')->nullable();
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
        Schema::dropIfExists('verifications');
    }
}
