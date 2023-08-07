<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PajakRekan extends Model
{
    use HasFactory;

    public $table = 'pajak_rekan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nama',
        'biaya_jasa',
        'jumlah_akta',
        'jasa_bruto',
        'dpp',
        'dpp_akumulasi',
        'pph_dipotong',
        'pajak_akumulasi',
        'transfer',
    ];

    public function pajakRekanAkta()
    {
        return $this->hasMany(PajakRekanAkta::class, 'pajak_rekan_id', 'id');
    }
}
