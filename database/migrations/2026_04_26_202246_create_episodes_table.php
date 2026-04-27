<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('id_integration');
            $table->foreignUuid('show_id')->constrained('shows')->onDelete('cascade');
            $table->string('name', 265)->nullable();
            $table->integer('season')->nullable();
            $table->integer('number')->nullable();
            $table->string('type', 265)->nullable();
            $table->string('airdate', 20)->nullable();
            $table->string('airtime', 20)->nullable();
            $table->string('airstamp', 50)->nullable();
            $table->integer('runtime')->nullable();
            $table->decimal('rating', 5, 2)->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
