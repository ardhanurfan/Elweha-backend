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
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('kategori_pengeluaran_id')->constrained('kategori_pengeluaran')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('jenis_pengeluaran_id')->constrained('jenis_pengeluaran')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('tanggal');
            $table->bigInteger('jumlah');
            $table->text('deskripsi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
