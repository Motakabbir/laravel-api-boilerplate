<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTotalProductsByCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('total_products_by_companies', function (Blueprint $table) {
            $table->integer('product_company_id')->nullable()->unsigned();
            $table->integer('total_inserted')->nullable();
            $table->integer('total_published')->nullable();
        });

        Schema::table('total_products_by_companies', function(Blueprint $table){
            $table->unique(['product_company_id']);
            $table->foreign('product_company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('total_products_by_companies');
    }
}
