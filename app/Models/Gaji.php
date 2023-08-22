<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
    use HasFactory;

    public $table = 'gaji';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nama_karyawan',
        'jenis_gaji',
        'besar_gaji',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'gaji_id', 'id');
    }

    public function skil()
    {
        return $this->hasMany(SkilBonus::class, 'gaji_id', 'id');
    }

    public function variabel()
    {
        return $this->hasMany(VariabelBonus::class, 'gaji_id', 'id');
    }
}
