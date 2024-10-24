<?php

namespace App\Models;

use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovementsModel extends SnipeModel
{
    use CompanyableTrait;
    use HasFactory, Loggable, Requestable, Presentable;
    ///
    protected $presenter = \App\Presenters\MovementPresenter::class;
    protected $table = 'movements';

    protected $fillable = [
        'project_id',
        'movement_number',
        'location_id',
        'movement_status_id',
        'company_id',
        'to',
        'person_charge',
        'notes',
        'datetime',
        'datetime_specific',
        'category_movement',
        'phone_person_in_charge',
        'contact_recipient'
    ];

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }

    public function status()
    {
        return $this->belongsTo(Statuslabel::class, 'movement_status_id');
    }

    public function project()
    {
        return $this->belongsTo(ProjectMovement::class, 'project_id');
    }

    /**
     * Establishes the asset -> company relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\BranchCompany::class, 'company_id');
    }

    public function getMovementAttribute()
    {
        return $this->movement_number . ' (' . $this->project->project_name . ')';
    }
}
