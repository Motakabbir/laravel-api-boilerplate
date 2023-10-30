<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionalBannerInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotional_banner_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('banner_title');
            $table->string('promotional_link')->nullable();
            $table->boolean('display_type')->default('1');
            $table->boolean('schedule_type')->default('0');
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->string('desktop_banner_image')->nullable();
            $table->string('mobile_banner_image')->nullable();
            $table->boolean('status')->default('1');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('promotional_banner_infos', function(Blueprint $table){
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotional_banner_infos');
    }
}
