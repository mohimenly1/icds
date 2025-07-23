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
        Schema::table('doctors', function (Blueprint $table) {
            // إضافة مفتاح خارجي لجدول الغرف
            // We check if the column doesn't exist before adding it
            if (!Schema::hasColumn('doctors', 'room_id')) {
                $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null')->after('status');
            }

            // حذف الأعمدة القديمة بعد التحقق من وجودها
            if (Schema::hasColumn('doctors', 'floor')) {
                $table->dropColumn('floor');
            }
            if (Schema::hasColumn('doctors', 'room_number')) {
                $table->dropColumn('room_number');
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            //
        });
    }
};
