<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalProductsByOperatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_products_by_operators', function (Blueprint $table) {
            $table->integer('user_id')->nullable()->unsigned();
            $table->integer('total_inserted')->nullable();
            $table->integer('total_published')->nullable();
        });

        Schema::table('total_products_by_operators', function(Blueprint $table){
            $table->unique(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total_products_by_operators');
    }
}
