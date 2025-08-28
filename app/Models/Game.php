<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $primayKey = "appid";

    public $incrementing = false;

    protected $fillable = [
        'appid',
        'name',
        'player_count',
    ];

    protected $casts = [
        'appid' => 'integer',
        'player_count' => 'integer',
    ];
}
