<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        User::create([
            'nama' => "admin",
            'username' => "admin",
            'password' => Hash::make("12345678"),
            'role' => "BOD",
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('nama', 'admin')->first()->forceDelete();
    }
};
