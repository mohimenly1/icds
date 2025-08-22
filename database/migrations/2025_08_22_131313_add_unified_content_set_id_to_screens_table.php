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
        Schema::table('screens', function (Blueprint $table) {
            $table->foreignId('unified_content_set_id')->nullable()->constrained()->onDelete('set null')->after('is_unified_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('screens', function (Blueprint $table) {
            //
        });
    }
};
