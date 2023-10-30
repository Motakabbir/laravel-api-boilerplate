<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderShipBillInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_ship_bill_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('order_id')->nullable()->unsigned();
            $table->text('shipping_address')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::table('order_ship_bill_infos', function(Blueprint $table){            
            $table->foreign('order_id')->references('id')->on('order_infos')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_ship_bill_infos');
    }
}
