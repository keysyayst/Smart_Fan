<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualControl extends Model
{
    use HasFactory;

    protected $table = 'manual_control';
    public $timestamps = false;

    protected $fillable = [
        'fan',
        'led'
    ];
}
