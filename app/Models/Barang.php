<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;
    public $table = 'barang';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'jenis_barang_id',
        'nama_barang',
        'jumlah',
        'satuan',
        'tanggal',
    ];

    public function jenis()
    {
        return $this->belongsTo(JenisBarang::class, 'jenis_barang_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
