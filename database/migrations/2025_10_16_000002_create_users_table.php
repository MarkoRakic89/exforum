<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('maticni_broj')->unique();
            $table->string('naziv');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('grad_id')->constrained('cities');
            $table->enum('status', ['active', 'inactive', 'locked'])->default('active');
            $table->boolean('is_featured')->default(false);
            $table->integer('featured_rank')->nullable();
            $table->decimal('avg_rating', 3, 2)->default(0);
            $table->integer('ratings_count')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};