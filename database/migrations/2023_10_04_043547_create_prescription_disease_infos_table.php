<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrescriptionDiseaseInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prescription_disease_infos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('prescription_id')->unsigned()->nullable();
            $table->integer('disease_id')->unsigned()->nullable();
        });

        Schema::table('prescription_disease_infos', function(Blueprint $table){
            $table->foreign('prescription_id')->references('id')->on('prescription_infos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('disease_id')->references('id')->on('disease_infos')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prescription_disease_infos');
    }
}
