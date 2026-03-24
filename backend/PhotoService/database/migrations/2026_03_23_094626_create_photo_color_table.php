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
       Schema::create('photo_color', function (Blueprint $table) {
            $table->uuid('photo_id');
            $table->uuid('color_id');

            $table->primary(['photo_id', 'color_id']);

            $table->foreign('photo_id')
                ->references('id')
                ->on('photo')
                ->cascadeOnDelete();

            $table->foreign('color_id')
                ->references('id')
                ->on('colors')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_color');
    }
};
