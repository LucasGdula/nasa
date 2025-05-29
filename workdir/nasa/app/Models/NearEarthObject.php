<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NearEarthObject extends Model
{
    use HasFactory;
    protected $fillable = [
        'ref_id',
        'name',
        'date',
        'estimated_diameter_min',
        'estimated_diameter_max',
        'is_hazardous',
        'abs_magnitude',
        'velocity',
        'miss_distance',
    ];

    public function analyses()
    {
        return $this->belongsToMany(
            Analysis::class,
            'near_earth_object_analysis',
            'neo_id',
            'analysis_id'
        );
    }
}
