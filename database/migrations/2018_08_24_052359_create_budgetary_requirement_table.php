<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBudgetaryRequirementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('linetb', function (Blueprint $table) {
            $table->increments('lineid');
            $table->integer('activityid');
            $table->integer('line2id');
            $table->string('lineitem');
            $table->float('peso');
            $table->float('dollar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('linetb');
    }
}
