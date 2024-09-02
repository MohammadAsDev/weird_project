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
            $table->bigInteger('clinic_id')->unsigned();
            $table->bigInteger('patient_id')->unsigned();

            $table->dateTime('date');
            $table->dateTime('next_date')->nullable();
            $table->integer('status')->default(AppointementStatus::WAITED->value);

            $table->index('status');
            $table->index('doctor_id');
            $table->index('clinic_id');
            $table->index('patient_id');

            $table->foreign('doctor_id')->references('user_id')->on('doctors');
            $table->foreign('clinic_id')->references('id')->on('clinics');
            $table->foreign('patient_id')->references('user_id')->on('patients');

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
            $table->dropIndex('doctor_id');
            $table->dropIndex('clinic_id');
            $table->dropIndex('patient_id');

            $table->dropForeign('doctor_id');
            $table->dropForeign('clinic_id');
            $table->dropForeign('patient_id');
        });

        Schema::dropIfExists('appointements');
    }
}
