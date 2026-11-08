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
            $table->bigIncrements('id_rekap'); // bigIncrements untuk BIGINT AUTO_INCREMENT PRIMARY KEY
            $table->unsignedInteger('petugas_id'); // unsignedInteger untuk Foreign Key
            $table->dateTime('tanggal_rekap');
            $table->integer('total_petugas_hadir')->default(0);
            $table->integer('total_petugas_ijin')->default(0);
            $table->integer('total_petugas_alpha')->default(0);
            $table->longText('isi_rekap'); // longText untuk LONGTEXT
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
        Schema::dropIfExists('rekap_ronda_harian');
    }
};