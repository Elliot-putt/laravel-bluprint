<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('users', function(Blueprint $table) {
            $table->longText('yesterdays_summary')->nullable()->after('github_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('yesterdays_summary');
        });
    }

};
