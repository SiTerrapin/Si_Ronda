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
            $table->increments('id_petugas');
            $table->string('username', 20)->unique();
            $table->string('password', 100);
            $table->string('nama_lengkap', 50);
            $table->string('no_hp', 20)->nullable();
            $table->string('alamat', 100)->nullable();
            $table->enum('gender', ['M', 'F'])->default('M');
            $table->enum('role', ['super_admin', 'admin', 'petugas'])->default('petugas')->index();
            $table->enum('is_active', ['0', '1'])->default('1')->index();
            $table->timestamps();
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
