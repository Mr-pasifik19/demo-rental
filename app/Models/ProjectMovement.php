<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMovement extends Model
{
    use HasFactory;

    protected $table = 'project_movements';

    protected $fillable = [
        'project_number', 'project_name',
        'person_id',
    ];

    public function person()
    {
        return $this->belongsTo(User::class, 'person_id');
    }
}
