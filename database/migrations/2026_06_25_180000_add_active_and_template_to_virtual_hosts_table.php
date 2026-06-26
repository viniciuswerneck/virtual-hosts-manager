<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('virtual_hosts', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('port');
            $table->string('template')->nullable()->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_hosts', function (Blueprint $table) {
            $table->dropColumn(['active', 'template']);
        });
    }
};
