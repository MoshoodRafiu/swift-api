<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('abbr');
            $table->text('logo');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('coins')->insert([
            'name' => 'Bitcoin',
            'slug' => 'bitcoin',
            'abbr' => 'btc',
            'logo' => 'bitcoin.png'
        ]);

        \Illuminate\Support\Facades\DB::table('coins')->insert([
            'name' => 'Ethereum',
            'slug' => 'ethereum',
            'abbr' => 'eth',
            'logo' => 'ethereum.png'
        ]);

        \Illuminate\Support\Facades\DB::table('coins')->insert([
            'name' => 'Bitcoin Cash',
            'slug' => 'bitcoin-cash',
            'abbr' => 'bch',
            'logo' => 'bitcoin-cash.png'
        ]);

        \Illuminate\Support\Facades\DB::table('coins')->insert([
            'name' => 'Litecoin',
            'slug' => 'litecoin',
            'abbr' => 'ltc',
            'logo' => 'litecoin.png'
        ]);

        \Illuminate\Support\Facades\DB::table('coins')->insert([
            'name' => 'Ripple',
            'slug' => 'ripple',
            'abbr' => 'xrp',
            'logo' => 'ripple.png'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coins');
    }
}
