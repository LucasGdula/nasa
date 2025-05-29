<?php

namespace Tests\Feature\Analysis;

use App\Models\NearEarthObject;
use App\Models\User;
use Tests\TestCase;

class AnalysisTest extends TestCase
{
    public function test_trigger_analysis()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/analysis/query', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-07'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([['date', 'total_count', 'avg_estimated_diameter_min', 'avg_estimated_diameter_max', 'max_velocity', 'smallest_miss_distance']]);
    }

    public function test_get_analysis_list()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/analysis/list');

        $response->assertStatus(200);
        $response->assertJsonStructure([['date', 'total_count', 'avg_estimated_diameter_min', 'avg_estimated_diameter_max', 'max_velocity', 'smallest_miss_distance']]);
    }

    public function test_get_neo_details()
    {
        $neo = NearEarthObject::factory()->create();
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/neo/' . $neo->id);

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'name', 'date', 'analyses']);
    }

    public function test_date_range_validation()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/analysis/query', [
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-23'
        ]);

        $response->assertStatus(400);
        $response->assertJson(['error' => 'Date range must not be greater than 7 days']);
    }
}
