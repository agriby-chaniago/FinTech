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
        if (Schema::hasColumn('users', 'keycloak_sub')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('keycloak_sub')->nullable()->after('email')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'keycloak_sub')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_keycloak_sub_unique');
            $table->dropColumn('keycloak_sub');
        });
    }
};
