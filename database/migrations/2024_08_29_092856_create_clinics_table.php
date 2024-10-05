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
            $table->bigInteger('departement_id')->unsigned();
            $table->string('clinic_code')->unique();
            
            $table->timestamps();

            $table->index('departement_id');
            $table->foreign('departement_id')->references('id')->on('departements')->onDelete('cascade');
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
