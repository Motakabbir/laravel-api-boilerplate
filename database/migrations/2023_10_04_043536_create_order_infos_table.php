<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id')->nullable();
            $table->integer('customer_id')->nullable()->unsigned();
            $table->float('total_amount')->nullable();            
            $table->float('discount')->nullable();
            $table->float('delivery_fee')->nullable();
            $table->float('vat_amount')->nullable();
            $table->float('total_payable')->nullable();
            $table->string('extra_instruction')->nullable();
            $table->tinyInteger('choose_payment_type')->nullable();
            $table->boolean('paid')->nullable();
            $table->string('order_from')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->boolean('status')->default('0');
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('order_infos', function(Blueprint $table){            
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_infos');
    }
}
