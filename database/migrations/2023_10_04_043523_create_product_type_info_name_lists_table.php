<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductTypeInfoNameListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_type_info_name_lists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_type_info_id')->unsigned()->nullable();
            $table->integer('product_type_name_id')->unsigned()->nullable();
        });

        Schema::table('product_type_info_name_lists', function(Blueprint $table){
            $table->unique(['product_type_info_id', 'product_type_name_id'], 'product_type_info_name_list_index_unique');
            $table->foreign('product_type_info_id')->references('id')->on('product_type_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_type_name_id')->references('id')->on('product_type_info_names')->onDelete('set null')->onUpdate('cascade');
        });        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_type_info_name_lists');
    }
}
