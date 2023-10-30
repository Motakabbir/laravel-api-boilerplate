<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTypePriceContentInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_type_price_content_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_price_info_id')->unsigned()->nullable();
            $table->integer('product_price_type_id')->unsigned()->nullable();
            $table->integer('product_type_id')->unsigned()->nullable();
            $table->integer('product_type_item_id')->unsigned()->nullable();
            $table->tinyInteger('product_color_id')->unsigned()->nullable();
            $table->integer('product_size_id')->unsigned()->nullable();
        });
        
        Schema::table('product_type_price_content_infos', function(Blueprint $table){
            $table->unique(['product_price_info_id', 'product_price_type_id', 'product_type_id', 'product_type_item_id', 'product_color_id', 'product_size_id'],'product_type_price_content_unique_key');
            $table->foreign('product_price_info_id')->references('id')->on('product_type_price_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_price_type_id')->references('id')->on('product_price_types')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('product_type_item_id')->references('id')->on('product_type_info_names')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('product_color_id')->references('id')->on('product_color_infos')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('product_size_id')->references('id')->on('product_size_infos')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_type_price_content_infos');
    }
}
