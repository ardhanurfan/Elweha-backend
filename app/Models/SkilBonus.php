<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkilBonus extends Model
{
    use HasFactory;

    public $table = 'skil_bonus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gaji_id',
        'nama_bonus',
        'besar_bonus',
    ];
}
