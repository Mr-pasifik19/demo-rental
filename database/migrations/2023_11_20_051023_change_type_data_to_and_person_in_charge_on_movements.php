<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTypeDataToAndPersonInChargeOnMovements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->text('to')->change();
            $table->text('person_charge')->change();
            $table->text('notes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->oldDataType('to')->change();
            $table->oldDataType('person_charge')->change();
            $table->oldDataType('notes')->change();
        });
    }
}
