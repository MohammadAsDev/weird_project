<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nurses', function (Blueprint $table) {

            $table->engine = 'InnoDB';

            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('departement_id')->unsigned();
            $table->bigInteger('doctor_id')->unsigned();

            $table->integer('specialization');
            $table->integer('rate');
            $table->string('short_description' , 500);
            $table->timestamps();

            $table->primary('user_id');

            $table->foreign("user_id")->references('id')->on('users');
   
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
        Schema::table('nurses' , function(Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropIndex('doctor_id');
            $table->dropForeign('doctor_id');
            $table->dropIndex('departement_id');
            $table->dropForeign('departement_id');
        });
        Schema::dropIfExists('nurses');
    }
}
