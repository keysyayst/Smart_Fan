<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualControl extends Model
{
    use HasFactory;

    protected $table = 'manual_control';
    // timestamps sudah aktif by default di Laravel

    protected $fillable = [
        'mode',
        'fan'
    ];
}
