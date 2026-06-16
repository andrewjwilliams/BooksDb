<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_ebooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->string('site_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_ebooks');
    }
};
