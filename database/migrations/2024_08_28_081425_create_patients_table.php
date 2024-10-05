<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsTable extends Migration     // This migration is deprecated
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned();
            $table->integer('blood_type');
            $table->boolean('aspirin_allergy');
            $table->timestamps();

            $table->primary('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('patients' , function(Blueprint $table) {
            $table->dropForeign('user_id');
        });
        Schema::dropIfExists('patients');
    }
}
