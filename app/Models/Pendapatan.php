<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    use HasFactory;

    public $table = 'pendapatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'kategori_pendapatan_id',
        'tanggal',
        'jumlah',
        'pengirim',
        'deskripsi',
    ];

    public function kategori()
    {
        return $this->hasOne(KategoriPendapatan::class, 'kategori_pendapatan_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }
}
