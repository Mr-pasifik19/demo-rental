<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->references('id')->on('project_movements');
            $table->string('movement_number');
            $table->unsignedBigInteger('location_id')->references('id')->on('locations');
            $table->unsignedBigInteger('movement_status_id')->references('id')->on('movement_statuses');
            $table->unsignedBigInteger('company_id')->references('id')->on('companies');
            $table->text('to');
            $table->text('person_charge');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('movements');
    }
}
