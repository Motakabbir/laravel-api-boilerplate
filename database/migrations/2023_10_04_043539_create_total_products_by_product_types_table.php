<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalProductsByProductTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_products_by_product_types', function (Blueprint $table) {
            $table->integer('product_type_id')->nullable()->unsigned();
            $table->integer('total_inserted')->nullable();
            $table->integer('total_published')->nullable();
        });

        Schema::table('total_products_by_product_types', function(Blueprint $table){
            $table->unique(['product_type_id']);
            $table->foreign('product_type_id')->references('id')->on('product_types')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total_products_by_product_types');
    }
}
