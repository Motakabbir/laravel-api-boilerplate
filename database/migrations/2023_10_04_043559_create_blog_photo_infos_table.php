<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogPhotoInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blog_photo_infos', function (Blueprint $table) {
            $table->Increments('id');
            $table->tinyInteger('blog_id')->unsigned()->nullable();
            $table->integer('photo_id')->unsigned()->nullable();
        });
        
        Schema::table('blog_photo_infos', function(Blueprint $table){
            $table->unique(['blog_id', 'photo_id']);
            $table->foreign('blog_id')->references('id')->on('blog_infos')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('blog_photo_infos');
    }
}
