<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_hosts', function (Blueprint $table) {
            $table->string('github_url')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_hosts', function (Blueprint $table) {
            $table->dropColumn('github_url');
        });
    }
};
