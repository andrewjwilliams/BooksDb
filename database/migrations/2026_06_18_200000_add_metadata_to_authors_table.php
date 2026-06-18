<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->string('fuller_name')->nullable()->after('name');
            $table->string('birth_date')->nullable()->after('fuller_name');
            $table->string('death_date')->nullable()->after('birth_date');
            $table->text('bio')->nullable()->after('death_date');
            $table->json('remote_ids')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn(['fuller_name', 'birth_date', 'death_date', 'bio', 'remote_ids']);
        });
    }
};
