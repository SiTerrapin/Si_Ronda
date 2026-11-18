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
        Schema::create('rekap_ronda_harian', function (Blueprint $table) {
            $table->bigIncrements('id_rekap');
            $table->unsignedInteger('petugas_id');
            $table->date('tanggal_rekap')->unique();
            $table->integer('total_petugas_terjadwal')->default(0);
            $table->integer('total_petugas_hadir')->default(0);
            $table->integer('total_petugas_ijin')->default(0);
            $table->integer('total_petugas_alpha')->default(0);
            $table->enum('status_keamanan', ['Aman', 'Waspada', 'Darurat'])->default('Aman');
            $table->longText('isi_rekap')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('rekap_ronda_harian');
    }
};
