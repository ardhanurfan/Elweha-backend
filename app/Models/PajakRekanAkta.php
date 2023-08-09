<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PajakRekanAkta extends Model
{
    use HasFactory;

    public $table = 'pajak_rekan_akta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'rekan_id',
        'tanggal',
        'no_awal',
        'no_akhir',
        'jumlah_akta',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function rekan()
    {
        return $this->belongsTo(Rekan::class, 'rekan', 'id');
    }
}
