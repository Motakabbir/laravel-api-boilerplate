<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTypePriceInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_type_price_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id')->unsigned()->nullable();
            $table->tinyInteger('price_index')->nullable();
            $table->decimal('unit_price',8,2)->nullable();
            $table->json('price_info')->nullable();
            $table->tinyInteger('cart_qty_mode')->nullable();
            $table->tinyInteger('product_color_id')->unsigned()->nullable();
            $table->integer('min_qty')->nullable();
            $table->integer('max_qty')->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('status')->default('1');
            $table->timestamps();
        });
        
        Schema::table('product_type_price_infos', function(Blueprint $table){
            $table->unique(['product_id', 'price_index'],'product_multi_price_unique_key');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_color_id')->references('id')->on('product_color_infos')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_type_price_infos');
    }
}
