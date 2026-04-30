<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('club_id');
            $table->foreign('club_id')->references('id')->on('clubs')->onDelete('restrict');

            $table->unsignedBigInteger('merchant_category_id');
            $table->foreign('merchant_category_id')->references('id')->on('merchant_categories')->onDelete('restrict');

            $table->string('name_en');
            $table->string('name_ar');
            $table->text('image');

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
        Schema::dropIfExists('merchants');
    }
}
