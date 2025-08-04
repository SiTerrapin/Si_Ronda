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
    Schema::create('anggota', function (Blueprint $table) {
        $table->id('id_petugas');
        $table->string('nama');
        $table->string('password');
        $table->string('alamat');
        $table->string('no_hp');
        $table->enum('is_active',[0,1]);
        $table->string('nama_lengkap');
        $table->enum('gender',['M','F']);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
