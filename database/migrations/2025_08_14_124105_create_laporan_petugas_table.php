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
        Schema::create('laporan_petugas', function (Blueprint $table) {
            $table->bigIncrements('id_lapor');
            $table->unsignedInteger('petugas_id');
            $table->unsignedInteger('plot_id')->nullable();
            $table->dateTime('tanggal_lapor')->index();
            $table->enum('kategori_situasi', ['Aman', 'Mencurigakan', 'Darurat/Bencana', 'Kriminalitas'])->default('Aman')->index();
            $table->longText('isi_laporan');
            $table->string('foto_bukti')->nullable();
            $table->enum('status_verifikasi', ['Pending', 'Diverifikasi'])->default('Pending')->index();
            $table->timestamps();

            $table->foreign('petugas_id')
                  ->references('id_petugas')
                  ->on('petugas')
                  ->onDelete('cascade');

            $table->foreign('plot_id')
                  ->references('id_plot')
                  ->on('plot_ronda')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_petugas');
    }
};
