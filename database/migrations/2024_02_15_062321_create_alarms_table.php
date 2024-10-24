<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlarmsTable extends Migration
{
    public function up()
    {
        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
            $table->string('task');
            $table->date('due_date');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alarms');
    }
}

