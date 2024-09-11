<?php

use App\Enums\AppointementStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointements', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('doctor_id')->unsigned();
            $table->bigInteger('patient_id')->unsigned();

            $table->dateTime('date');
            $table->string("period");
            $table->integer('status')->default(AppointementStatus::NEED_ACK->value);

            $table->index('status');
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
        Schema::drop('appointements' , function(Blueprint $table) {
            $table->dropIndex('status');
            
            $table->dropIndex('doctor_id');
            $table->dropIndex('patient_id');

            $table->dropForeign('doctor_id');
            $table->dropForeign('patient_id');
        });

        Schema::dropIfExists('appointements');
    }
}
