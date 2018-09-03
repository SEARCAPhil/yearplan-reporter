<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStrategicObjectiveTable extends Migration
{
    public function up()
    {
        Schema::create('objectivetb', function (Blueprint $table) {
            $table->increments('objectid');
            $table->integer('userid');
            $table->integer('fyid');
            $table->string('objectives');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('objectivetb');
    }
}
