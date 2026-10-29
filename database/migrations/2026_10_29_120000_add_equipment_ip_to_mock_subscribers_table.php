<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mock_subscribers', function (Blueprint $table) {
            $table->string('equipment_ip', 45)->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('mock_subscribers', function (Blueprint $table) {
            $table->dropColumn('equipment_ip');
        });
    }
};
