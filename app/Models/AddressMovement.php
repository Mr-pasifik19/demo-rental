<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressMovement extends Model
{
    use HasFactory;

    protected $table = 'address_movements';

    protected $fillable = [
        'address', 'project_movement_id'
    ];

    public function projects()
    {
        return $this->belongsTo(ProjectMovement::class, 'project_movement_id');
    }
}
