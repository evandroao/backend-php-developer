<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cria o enum type no PostgreSQL
        // DB::statement("CREATE TYPE role_enum AS ENUM ('ADMIN', 'USER')");

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username', 100)->unique();
            $table->string('password', 255);
            $table->enum('role', ['ADMIN', 'USER'])->default('USER');
            // Usa o tipo nativo do PostgreSQL
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        // Adiciona coluna role com tipo enum nativo
        // DB::statement("ALTER TABLE users ADD COLUMN role role_enum NOT NULL DEFAULT 'USER'");

        // Seed admin user (password: admin)
        DB::table('users')->insert([
            'id' => '08b5a3a9-8874-4fd7-b79a-45c877a65f6e',
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'role' => 'ADMIN',
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        // DB::statement("DROP TYPE IF EXISTS role_enum");
    }
};
