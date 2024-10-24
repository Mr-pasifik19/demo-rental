<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLengthTypeDataColumnProjectNumberInProjectMovements extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_movements', function (Blueprint $table) {
            $table->string('project_number')->change();
            $table->string('project_name')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_movements', function (Blueprint $table) {
            $table->oldDataType('project_number')->change();
            $table->oldDataType('project_name')->change();
        });
    }
}
