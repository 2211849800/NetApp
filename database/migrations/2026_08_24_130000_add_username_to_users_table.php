<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
        });

        $users = DB::table('users')->whereNull('username')->orderBy('id')->get();

        foreach ($users as $user) {
            $base = str($user->email)->before('@')->slug('_')->toString();
            $username = $base !== '' ? $base : 'user_'.$user->id;

            $counter = 1;
            while (DB::table('users')->where('username', $username)->exists()) {
                $username = ($base !== '' ? $base : 'user_'.$user->id).'_'.$counter;
                $counter++;
            }

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
