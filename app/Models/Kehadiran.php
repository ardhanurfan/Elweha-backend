<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    use HasFactory;

    public $table = 'kehadiran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gaji_id',
        'bulan',
        'tahun',
        'besar_gaji',
        'kehadiran_actual',
        'kehadiran_standart',
        'keterlambatan',
    ];

    public function gaji()
    {
        return $this->belongsTo(Gaji::class, 'gaji_id', 'id');
    }

    public function skil()
    {
        return $this->hasMany(SkilBonus::class, 'kehadiran_id', 'id');
    }

    public function variabel()
    {
        return $this->hasMany(VariabelBonus::class, 'kehadiran_id', 'id');
    }
}
