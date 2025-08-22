<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_screen', function (Blueprint $table) {
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->foreignId('screen_id')->constrained()->onDelete('cascade');
            $table->primary(['media_id', 'screen_id']);
            $table->integer('order')->default(0); // لترتيب عرض الوسائط على الشاشة
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_screen');
    }
};
