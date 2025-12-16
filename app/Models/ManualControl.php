<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualControl extends Model
{
    use HasFactory;

    protected $table = 'manual_control';

    protected $fillable = [
        'fan',
        'led'
    ];
    public $timestamps = false;
}
