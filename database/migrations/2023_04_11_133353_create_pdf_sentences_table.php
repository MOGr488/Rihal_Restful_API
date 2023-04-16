<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf_sentences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pdf_file_id')->references('id')->on('pdf_files')->onDelete('cascade');
            $table->text('sentence');
            $table->integer('page_number');
    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pdf_sentences');
    }
};
