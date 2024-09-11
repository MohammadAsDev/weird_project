<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutineTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routine_tests', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('doctor_id')->unsigned();
            $table->bigInteger('patient_id')->unsigned();

            $table->decimal('breathing_rate');
            $table->decimal('pulse_rate');
            $table->decimal('body_temperature');    // in C

            $table->string('medical_notes')->nullable();
            $table->string('prescription')->nullable();

            $table->index('doctor_id');
            $table->index('patient_id');

            $table->foreign('doctor_id')->references('user_id')->on('doctors');
            $table->foreign('patient_id')->references('id')->on('users');
            
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
        Schema::drop('routine_tests' , function(Blueprint $table) {
            $table->dropIndex('doctor_id');
            $table->dropIndex('patient_id');

            $table->dropForeign('doctor_id');
            $table->dropForeign('patient_id');
        });
        
        Schema::dropIfExists('routine_tests');
    }
}
