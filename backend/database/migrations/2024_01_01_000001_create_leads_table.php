<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->text('bio')->nullable();
            $table->string('country')->nullable();
            $table->string('gender')->nullable();
            $table->string('source_keyword');
            $table->enum('tag', ['hot', 'warm', 'cold'])->default('cold');
            $table->text('notes')->nullable();
            $table->integer('score')->default(0);
            $table->boolean('is_contacted')->default(false);
            $table->timestamps();

            $table->index('country');
            $table->index('tag');
            $table->index('gender');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
