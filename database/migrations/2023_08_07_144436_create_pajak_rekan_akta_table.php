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
        Schema::create('pajak_rekan_akta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('pajak_rekan_id')->nullable()->constrained('pajak_rekan')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamp('tanggal');
            $table->integer('no_awal');
            $table->integer('no_akhir');
            $table->integer('jumlah_akta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajak_rekan_akta');
    }
};
