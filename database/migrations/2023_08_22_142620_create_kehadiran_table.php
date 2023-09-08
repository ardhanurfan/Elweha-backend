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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gaji_id')->constrained('gaji')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('bulan')->default(0);
            $table->integer('tahun')->default(0);
            $table->bigInteger('besar_gaji');
            $table->integer('kehadiran_actual')->default(0);
            $table->integer('kehadiran_standart')->default(0);
            $table->integer('keterlambatan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
