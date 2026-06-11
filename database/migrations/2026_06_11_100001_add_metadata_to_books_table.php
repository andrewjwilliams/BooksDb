<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->unsignedSmallInteger('publish_year')->nullable();
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->string('language', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['publish_year', 'page_count', 'language']);
        });
    }
};
