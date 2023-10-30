<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDeliveryPersonInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_delivery_person_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->nullable()->unsigned();
            $table->integer('delivery_person_id')->nullable()->unsigned();
            $table->tinyInteger('rating_points')->nullable();
            $table->string('review_comments')->nullable();
            $table->boolean('status')->default('0');
            $table->timestamps();
        });

        Schema::table('order_delivery_person_infos', function(Blueprint $table){
            $table->foreign('order_id')->references('id')->on('order_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('delivery_person_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_delivery_person_infos');
    }
}
