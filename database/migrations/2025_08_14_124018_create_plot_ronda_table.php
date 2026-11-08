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
        Schema::create('plot_ronda', function (Blueprint $table) {
            $table->increments('id_plot'); // Menggunakan increments untuk INT AUTO_INCREMENT PRIMARY KEY
            $table->unsignedInteger('petugas_id'); // unsignedInteger untuk Foreign Key
            $table->string('nama_hari', 20);
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->enum('is_leader', ['0', '1'])->default('0');
            // Untuk created_at dengan DEFAULT CURRENT_TIMESTAMP
            $table->timestamp('created_at')->useCurrent();
            // Jika Anda juga butuh updated_at, gunakan $table->timestamps()
            // $table->timestamps();

            // Definisi Foreign Key
            $table->foreign('petugas_id')
                  ->references('id_petugas')
                  ->on('petugas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plot_ronda');
    }
};