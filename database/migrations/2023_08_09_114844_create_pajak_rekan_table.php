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
        Schema::create('pajak_rekan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('rekan_id')->nullable()->constrained('rekan')->onDelete('cascade')->onUpdate('cascade');
            $table->double('jumlah_akta')->default(0);
            $table->double('jasa_bruto')->default(0);
            $table->double('dpp')->default(0);
            $table->double('dpp_akumulasi')->default(0);
            $table->double('pph_dipotong')->default(0);
            $table->double('pajak_akumulasi')->default(0);
            $table->double('transfer')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajak_rekan');
    }
};
