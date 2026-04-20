<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_id');

            $table->string('referral_code');

            $table->string('device_id')->nullable();
            $table->string('ip_address')->nullable();

            $table->timestamps();

            // علاقات
            $table->foreign('referrer_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('referred_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            // منع التكرار
            $table->unique(['referrer_id', 'referred_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referrals');
    }
}
