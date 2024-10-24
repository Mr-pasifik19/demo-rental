<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'address', 'company_name'
    ];
}
