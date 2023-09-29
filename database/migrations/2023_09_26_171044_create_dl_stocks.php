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
        Schema::create('dl_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->nullable();
            $table->string('dl_number', 40)->nullable();
            $table->string('name', 70)->nullable();
            $table->string('father_name', 70)->nullable();
            $table->date('dob')->nullable();
            $table->string('blood', 3)->nullable();
            $table->integer('box_number')->nullable();
            $table->dateTime('receive_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->string('comment', 255)->nullable();
            $table->integer('creator_id')->nullable();
            $table->integer('editor_id')->nullable();
            $table->integer('delivered_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dl_stocks');
    }
};
