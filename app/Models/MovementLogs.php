<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementLogs extends Model 
{
    use HasFactory;
    
    protected $fillable = [
        'movement_id',
        'activity'
    ];

    protected $table = 'movement_log';
}