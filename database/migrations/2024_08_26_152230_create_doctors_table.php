<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doctors', function (Blueprint $table) {
            
            $table->engine = 'InnoDB';

            $table->bigInteger("user_id")->unsigned();
            $table->bigInteger('departement_id')->unsigned()->nullable();

            $table->date('assigned_at');
            $table->integer('specialization');
            $table->string('short_description' , 500);
            $table->integer('rate');
            $table->timestamps();


            $table->primary('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('departement_id');
            $table->foreign('departement_id')->references('id')->on('departements')->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('doctors' , function(Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropIndex('departement_id');
            $table->dropForeign('departement_id');
        });
        Schema::dropIfExists('doctors');
    }
}
