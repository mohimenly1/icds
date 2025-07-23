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
            // تأكد من أن اسم المفتاح الخارجي صحيح
            // قد يكون 'doctors_room_id_foreign'
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('set null');
        });
    }
};
