<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('users', function(Blueprint $table) {
            $table->string('github_id')->nullable()->after('remember_token');
            $table->string('github_username')->nullable()->after('github_id');
            $table->string('github_avatar')->nullable()->after('github_username');
            $table->string('github_access_token')->nullable()->after('github_avatar');
            $table->string('github_refresh_token')->nullable()->after('github_access_token');
            $table->timestamp('github_expires_at')->nullable()->after('github_refresh_token');

        });
    }

    public function down(): void
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('github_id');
            $table->dropColumn('github_username');
            $table->dropColumn('github_avatar');
            $table->dropColumn('github_access_token');
            $table->dropColumn('github_refresh_token');
            $table->dropColumn('github_expires_at');
        });
    }

};
