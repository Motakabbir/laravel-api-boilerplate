<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFcmTokenInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fcm_token_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('app_platform')->nullable();
            // $table->text('user_agent_info')->nullable();
            // $table->string('ip_addr')->nullable();
            $table->string('token')->nullable();
            $table->timestamps();
        });

        Schema::table('fcm_token_infos', function(Blueprint $table){            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fcm_token_infos');
    }
}
