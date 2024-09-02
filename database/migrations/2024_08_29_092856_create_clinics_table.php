<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClinicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->integer('clinic_type');
            $table->bigInteger('doctor_id')->unsigned();

            // internal clinics
            $table->bigInteger('departement_id')->unsigned()->nullable();
            $table->string('clinic_code')->nullable();

            // external clinics
            $table->decimal('clinic_latitude')->nullable();
            $table->decimal('clinic_longitude')->nullable();
            
            $table->timestamps();

            $table->index('doctor_id');
            $table->foreign('doctor_id')->references('user_id')->on('doctors');
            
            $table->index('departement_id');
            $table->foreign('departement_id')->references('id')->on('departements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('clinics' , function(Blueprint $table) {
            $table->dropForeign('doctor_id');
            $table->dropIndex('departement_id');
            $table->dropForeign('departement_id');
        });
        Schema::dropIfExists('clinics');
    }
}
