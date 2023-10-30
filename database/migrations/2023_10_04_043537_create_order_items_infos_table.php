<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderItemsInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->nullable()->unsigned();
            $table->integer('product_id')->nullable()->unsigned();
            $table->string('product_title')->nullable();
            $table->integer('product_price_type_id')->nullable()->unsigned();
            $table->string('product_price_type')->nullable();
            $table->json('product_type_infos')->nullable();
            $table->integer('custom_product_design_id')->nullable()->unsigned();
            $table->string('upload_product_image')->nullable();
            $table->decimal('price',8,2)->nullable();
            $table->decimal('custom_product_design_price',8,2)->nullable();
            $table->integer('qty')->nullable();
            $table->timestamps();
        });

        Schema::table('order_items_infos', function(Blueprint $table){
            // $table->unique(['order_id','product_id','product_price_type_id']);
            $table->foreign('order_id')->references('id')->on('order_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('product_price_type_id')->references('id')->on('product_price_types')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('custom_product_design_id')->references('id')->on('media_galleries')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items_infos');
    }
}
