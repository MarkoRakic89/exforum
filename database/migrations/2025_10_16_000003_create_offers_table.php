<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('type', ['sell', 'buy']);
            $table->decimal('amount_eur', 12, 2);
            $table->decimal('percent', 5, 2);
            $table->enum('repeat_type', ['once', 'monthly'])->default('once');
            $table->date('repeat_until')->nullable();
            $table->enum('status', ['published','reserved_partial','reserved_full','in_progress','completed','canceled'])->default('published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};