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
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "شاشة الاستقبال الرئيسية"
            $table->string('location')->nullable(); // e.g., "الطابق الثاني - قسم الأطفال"
            $table->string('unique_key')->unique(); // مفتاح فريد لكل شاشة للوصول إليها عبر الرابط
            $table->boolean('is_active')->default(true);
            $table->boolean('is_unified_content')->default(false); // لتفعيل ميزة المحتوى الموحد
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screens');
    }
};
