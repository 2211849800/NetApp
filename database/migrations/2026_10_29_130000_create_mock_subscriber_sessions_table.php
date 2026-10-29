<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_subscriber_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->index();
            $table->string('session_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 32)->nullable();
            $table->string('access_point')->nullable();
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->nullable();
            $table->decimal('download_gb', 10, 3)->default(0);
            $table->decimal('upload_gb', 10, 3)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_subscriber_sessions');
    }
};
