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
        Schema::create('variabel_bonus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kehadiran_id')->constrained('kehadiran')->onDelete('cascade')->onUpdate('cascade');
            $table->string('nama_bonus');
            $table->bigInteger('besar_bonus');
            $table->bigInteger('jumlah');
            $table->bigInteger('total');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variabel_bonus');
    }
};
