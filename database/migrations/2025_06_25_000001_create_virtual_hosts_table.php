<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_hosts', function (Blueprint $table) {
            $table->id();
            $table->string('server_name')->unique();
            $table->string('document_root');
            $table->boolean('ssl_enabled')->default(true);
            $table->integer('port')->default(80);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_hosts');
    }
};
