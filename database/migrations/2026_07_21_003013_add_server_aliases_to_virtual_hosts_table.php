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
        Schema::table('virtual_hosts', function (Blueprint $table) {
            $table->text('server_aliases')->nullable()->after('server_name');
        });
    }

    public function down(): void
    {
        Schema::table('virtual_hosts', function (Blueprint $table) {
            $table->dropColumn('server_aliases');
        });
    }
};
