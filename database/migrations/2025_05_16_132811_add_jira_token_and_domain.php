<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('users', function(Blueprint $table) {
            $table->longText('jira_token')->after('github_access_token')->nullable();
            $table->string('jira_domain')->after('jira_token')->nullable();
            $table->string('jira_connected_email')->after('jira_domain')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('jira_token');
            $table->dropColumn('jira_domain');
        });
    }

};
