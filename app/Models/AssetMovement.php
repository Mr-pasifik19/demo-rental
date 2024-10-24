<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    use HasFactory;


    protected $fillable = [
        'asset_id',
        'movement_id',
    ];

    public function assets()
    {
        return $this->hasMany(\App\Models\Asset::class, 'asset_id');
    }

    public function movements()
    {
        return $this->belongsTo(MovementsModel::class, 'movement_id');
    }
}
