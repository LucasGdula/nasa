<?php

namespace App\Services\NASA;

use App\Clients\NASA\NearEarthObjectsApiClient as NearEarthObjectsApiClient;
use App\Models\Analysis;
use App\Models\NearEarthObject;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class NearEarthObjectsService
{
    protected NearEarthObjectsApiClient $apiClient;

    public function __construct(NearEarthObjectsApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function fetch($startDate, $endDate): array
    {
        $totalEntries = 0;
        $newEntries = 0;
        $updatedEntries = 0;

        try {

            $data = $this->apiClient->fetchObjects($startDate, $endDate);

            DB::beginTransaction();

            foreach ($data['near_earth_objects'] ?? [] as $date => $neoList) {
                foreach ($neoList as $neoData) {

                    $totalEntries++;

                    $velocities = [];
                    $missDistances = [];

                    foreach ($neoData['close_approach_data'] ?? [] as $approach) {
                        $velocities[] = (float)$approach['relative_velocity']['kilometers_per_second'] * 1000;
                        $missDistances[] = (float)$approach['miss_distance']['kilometers'] * 1000;
                    }

                    $maxVelocity = !empty($velocities) ? max($velocities) : 0;
                    $smallestMissDistance = !empty($missDistances) ? min($missDistances) : 0;

                    $result = NearEarthObject::firstOrCreate(
                        ['ref_id' => $neoData['neo_reference_id']],
                        [
                            'name' => $neoData['name'],
                            'date' => $date,
                            'estimated_diameter_min' => $neoData['estimated_diameter']['meters']['estimated_diameter_min'],
                            'estimated_diameter_max' => $neoData['estimated_diameter']['meters']['estimated_diameter_max'],
                            'is_hazardous' => $neoData['is_potentially_hazardous_asteroid'],
                            'abs_magnitude' => $neoData['absolute_magnitude_h'],
                            'velocity' => $maxVelocity,
                            'miss_distance' => $smallestMissDistance,
                        ]
                    );

                    $result->wasRecentlyCreated ? $newEntries++ : $updatedEntries++;
                }
            }

            DB::commit();

            return [
                'fetched' => $totalEntries,
                'new' => $newEntries,
                'skipped' => $updatedEntries,
            ];

        } catch ( Exception $e) {
            DB::rollBack();
            throw new Exception("Failed to fetch and store NEO Entries: " . $e->getMessage());
        }
    }

    public function analyse($startDate, $endDate): bool
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $dates = collect();
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates->push($date->toDateString());
        }

        foreach ($dates as $date) {
            $entries = NearEarthObject::whereDate('date', $date)->get();

            if ($entries->isEmpty()) {
                continue;
            }

            $avgDiameterMin = $entries->avg('estimated_diameter_min');
            $avgDiameterMax = $entries->avg('estimated_diameter_max');
            $maxVelocity = $entries->max('velocity') ?? 0;
            $smallestMissDistance = $entries->min('miss_distance') ?? 0;
            $analysis = Analysis::updateOrCreate(
                ['date' => $date],
                [
                    'date' => $date,
                    'total_count' => $entries->count(),
                    'avg_estimated_diameter_min' => $avgDiameterMin,
                    'avg_estimated_diameter_max' => $avgDiameterMax,
                    'max_velocity' => $maxVelocity,
                    'smallest_miss_distance' => $smallestMissDistance,
                ]
            );

            $analysis->nearEarthObjects()->sync($entries->pluck('id'));
        }

        return true;
    }
}
