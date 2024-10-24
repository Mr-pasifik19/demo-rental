<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->references('id')->on('assets');
            $table->unsignedBigInteger('movement_id')->references('id')->on('movements');
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
        Schema::dropIfExists('asset_movements');
    }
}
