<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->json('questions');
            $table->unsignedBigInteger('creator_id')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('forms');
    }
};
