<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operational_notifications', function (Blueprint $table) {
            $table->string('contract_number')->nullable()->index();
            $table->string('notification_key')->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operational_notifications', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropUnique(['notification_key']);
            $table->dropIndex(['contract_number']);
            $table->dropColumn(['contract_number', 'notification_key', 'created_by']);
        });
    }
};
