<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomProductDesignInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_product_design_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('design_title');
            $table->integer('cat_id')->unsigned()->nullable();
            $table->decimal('price',10,2)->nullable();
            $table->boolean('status')->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        
        Schema::table('custom_product_design_infos', function(Blueprint $table){
            $table->foreign('cat_id')->references('id')->on('categories')->onDelete('set null')->onUpdate('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('custom_product_design_infos');
    }
}
