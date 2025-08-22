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
        Schema::create('media_unified_content_set', function (Blueprint $table) {
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->foreignId('unified_content_set_id')->constrained()->onDelete('cascade');
            $table->primary(['media_id', 'unified_content_set_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_unified_content_set');
    }
};
