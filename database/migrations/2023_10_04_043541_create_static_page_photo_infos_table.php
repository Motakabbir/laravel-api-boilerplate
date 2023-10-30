<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStaticPagePhotoInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('static_page_photo_infos', function (Blueprint $table) {
            $table->Increments('id');
            $table->tinyInteger('static_page_id')->unsigned()->nullable();
            $table->integer('photo_id')->unsigned()->nullable();
        });

        Schema::table('static_page_photo_infos', function(Blueprint $table){
            $table->unique(['static_page_id', 'photo_id']);
            $table->foreign('static_page_id')->references('id')->on('static_page_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('photo_id')->references('id')->on('media_galleries')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('static_page_photo_infos');
    }
}
