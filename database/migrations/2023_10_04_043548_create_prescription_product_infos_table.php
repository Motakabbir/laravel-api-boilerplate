<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrescriptionProductInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prescription_product_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('prescription_id')->unsigned()->nullable();
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('taking_schedule')->nullable();
            $table->tinyInteger('taking_type')->nullable();
            $table->date('last_taking_date')->nullable();
        });

        Schema::table('prescription_product_infos', function(Blueprint $table){
            $table->foreign('prescription_id')->references('id')->on('prescription_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prescription_product_infos');
    }
}
