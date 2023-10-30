<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_galleries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cat_id')->unsigned()->nullable();
            $table->string('content')->nullable();
            $table->string('content_title')->unique();
            $table->tinyInteger('content_type')->nullable();
            $table->integer('content_size')->nullable();
            $table->boolean('watermark')->default(0);
            $table->string('watermark_pos',15);
            $table->boolean('status')->default(0);
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('media_galleries', function(Blueprint $table){
            $table->foreign('cat_id')->references('id')->on('media_categories')->onDelete('set null')->onUpdate('cascade');
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
        Schema::dropIfExists('media_galleries');
    }
}
