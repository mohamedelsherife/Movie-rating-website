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
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('director_id')->constrained('directors')
                  ->restrictOnDelete();
            $table->unsignedSmallInteger('start_year');
            $table->unsignedSmallInteger('end_year')->nullable(); // null = still running
            $table->unsignedSmallInteger('seasons');
            $table->unsignedSmallInteger('episodes')->nullable();
            $table->text('description')->nullable();
            $table->string('poster')->nullable();
            $table->timestamps();
            $table->index('start_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
