<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManageAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manage_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable()->unsigned();
            $table->string('full_name')->nullable();
            $table->string('contact_no')->nullable();
            $table->integer('region_id')->nullable()->unsigned();
            $table->integer('city_id')->nullable()->unsigned();
            $table->integer('area_id')->nullable()->unsigned();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->text('address')->nullable();
            $table->tinyInteger('label_id')->nullable();
            $table->boolean('default_shipping_address')->default(0);
            $table->boolean('default_billing_address')->default(0);
            $table->boolean('status')->default(1);
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('updated_by')->nullable()->unsigned();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('manage_addresses', function(Blueprint $table){            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null')->onUpdate('cascade');
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
        Schema::dropIfExists('manage_addresses');
    }
}
