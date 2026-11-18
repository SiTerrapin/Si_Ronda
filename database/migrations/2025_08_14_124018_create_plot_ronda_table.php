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
            $table->increments('id_plot');
            $table->unsignedInteger('petugas_id');
            $table->string('nama_hari', 20)->index();
            $table->date('tanggal_ronda')->nullable()->index();
            $table->string('pos_jaga', 50)->default('Pos Utama');
            $table->string('shift', 30)->default('Malam (22:00 - 04:00)');
            $table->enum('is_leader', ['0', '1'])->default('0');
            $table->enum('is_active', ['0', '1'])->default('1')->index();
            $table->enum('status_kehadiran', ['Terjadwal', 'Hadir', 'Izin', 'Alfa'])->default('Terjadwal');
            $table->timestamps();

            $table->unique(['petugas_id', 'nama_hari', 'shift'], 'plot_petugas_hari_shift_unique');

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
