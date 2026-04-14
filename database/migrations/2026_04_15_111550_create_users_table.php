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
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('image')->nullable();
            $table->date('birthdate');
            $table->unsignedBigInteger('gender_id')->nullable();
            $table->foreign('gender_id')->references('id')->on('genders')->onDelete('restrict');
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('restrict');
            $table->unsignedBigInteger('governorate_id')->nullable();
            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('restrict');
            $table->unsignedBigInteger('area_id')->nullable();
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('restrict');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
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
