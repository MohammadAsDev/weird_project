<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('password');
            
            $table->date('birth_date');
            $table->integer('gender');
            $table->string('address');
            $table->string('profile_picture')->default('user_template.png');
            $table->integer('role_id');

            $table->string('ssn')->nullable();


            $table->timestamp('email_verified_at')->nullable();

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
        Schema::dropIfExists('users');
    }
}
