<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalProductsByCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_products_by_categories', function (Blueprint $table) {
            $table->integer('category_id')->nullable()->unsigned();
            $table->integer('total_inserted')->nullable();
            $table->integer('total_published')->nullable();
        });

        Schema::table('total_products_by_categories', function(Blueprint $table){
            $table->unique(['category_id']);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total_products_by_categories');
    }
}
