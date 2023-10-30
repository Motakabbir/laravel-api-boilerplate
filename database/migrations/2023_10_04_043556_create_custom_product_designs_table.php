<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomProductDesignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_product_designs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cpdi_id')->unsigned()->nullable();
            $table->integer('product_photo_id')->unsigned()->nullable();
        });
        
        Schema::table('custom_product_designs', function(Blueprint $table){
            $table->foreign('cpdi_id')->references('id')->on('custom_product_design_infos')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('custom_product_designs');
    }
}
