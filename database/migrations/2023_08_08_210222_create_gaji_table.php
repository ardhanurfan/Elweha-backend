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
        Schema::create('gaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->string('nama_karyawan');
            $table->integer('kehadiran')->default(0);
            $table->string('jenis_gaji');
            $table->integer('jumlah_gaji');
            $table->integer('jumlah_bonus')->default(0);
            $table->integer('pph_dipotong')->default(0);
            $table->integer('pajak_akumulasi')->default(0);
            $table->integer('transfer')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji');
    }
};
