<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class NearEarthObjectAnalysis extends Pivot
{
    public function analyses()
    {
        return $this->belongsToMany(Analysis::class, 'near_earth_object_analysis')
            ->using(NearEarthObjectAnalysis::class);
    }

    public function nearEarthObjects()
    {
        return $this->belongsToMany(NearEarthObject::class, 'near_earth_object_analysis')
            ->using(NearEarthObjectAnalysis::class);
    }
}
