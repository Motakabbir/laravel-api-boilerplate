<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTypePhotoInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::create('product_type_photo_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_price_info_id')->unsigned()->nullable();
            $table->integer('product_photo_id')->unsigned()->nullable();
        });

        Schema::table('product_type_photo_infos', function(Blueprint $table){
            $table->unique(['product_price_info_id', 'product_photo_id'],'product_type_photo_unique_key');
            $table->foreign('product_price_info_id')->references('id')->on('product_type_price_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_photo_id')->references('id')->on('media_galleries')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_type_photo_infos');
    }
}
