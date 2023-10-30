<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_infos', function (Blueprint $table) {
            $table->increments('id');            
            $table->integer('user_id')->nullable()->unsigned();
            $table->string('full_name')->nullable();            
            $table->string('pass_code')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('spouse_name')->nullable();
            $table->tinyInteger('gender_id')->nullable();
            $table->text('social_id_info')->nullable();
            $table->string('designation')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('contact_number')->nullable();
            $table->date('dob')->nullable();
            $table->string('nid')->nullable();
            $table->string('passport')->nullable();            
            $table->string('photo')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('contact_number');
            $table->index('dob');
            $table->index('nid');
            $table->index('passport');
        });

        Schema::table('user_infos', function(Blueprint $table){
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_infos');
    }
};
