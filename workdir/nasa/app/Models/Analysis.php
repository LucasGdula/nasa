<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analysis extends Model
{
    protected $fillable = [
        'date',
        'total_count',
        'avg_estimated_diameter_min',
        'avg_estimated_diameter_max',
        'max_velocity',
        'smallest_miss_distance'
    ];

    public function nearEarthObjects()
    {
        return $this->belongsToMany(
            NearEarthObject::class,
            'near_earth_object_analysis',
            'analysis_id',
            'neo_id'
        );
    }
}
