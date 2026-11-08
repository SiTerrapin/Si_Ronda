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
        Schema::create('petugas', function (Blueprint $table) {
            $table->increments('id_petugas'); // Menggunakan increments untuk INT AUTO_INCREMENT PRIMARY KEY
            $table->string('username', 20)->unique(); // username harus unik
            $table->string('password', 100);
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->string('nama_lengkap', 50);
            $table->enum('gender', ['M', 'F']);
            // Tidak perlu timestamps() karena di SQL asli tidak ada created_at/updated_at default
            // Tapi jika Anda ingin menggunakan fitur timestamps Laravel, Anda bisa menambahkannya:
            // $table->timestamps(); // Ini akan otomatis membuat created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petugas');
    }
};