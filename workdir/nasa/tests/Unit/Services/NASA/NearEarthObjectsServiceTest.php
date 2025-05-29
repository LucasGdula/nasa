<?php

namespace Tests\Unit\Services\NASA;

use App\Clients\NASA\NearEarthObjectsApiClient;
use App\Models\Analysis;
use App\Models\NearEarthObject;
use App\Services\NASA\NearEarthObjectsService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NearEarthObjectsServiceTest extends TestCase
{
    use RefreshDatabase;

    private $mockApiClient;
    private NearEarthObjectsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApiClient = $this->createMock(NearEarthObjectsApiClient::class);
        $this->service = new NearEarthObjectsService($this->mockApiClient);
    }

    public function test_fetch_and_stores_new_entries()
    {
        $sampleData = [
            'near_earth_objects' => [
                '2025-01-01' => [
                    [
                        'neo_reference_id' => '123',
                        'name' => 'Test Asteroid',
                        'estimated_diameter' => ['meters' => ['estimated_diameter_min' => 10, 'estimated_diameter_max' => 20]],
                        'is_potentially_hazardous_asteroid' => true,
                        'absolute_magnitude_h' => 15.5,
                        'close_approach_data' => [
                            [
                                'relative_velocity' => ['kilometers_per_second' => '5'],
                                'miss_distance' => ['kilometers' => '1000']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->mockApiClient->method('fetchObjects')
            ->willReturn($sampleData);

        $result = $this->service->fetch('2025-01-01', '2025-01-01');

        $this->assertEquals(1, $result['fetched']);
        $this->assertEquals(1, $result['new']);
        $this->assertEquals(0, $result['skipped']);

        $neo = NearEarthObject::first();
        $this->assertEquals('123', $neo->ref_id);
        $this->assertEquals(5000, $neo->velocity); // 5 km/s * 1000
    }

    public function test_existing_entries()
    {
        NearEarthObject::factory()->create(
            [
                'ref_id' => '123',
                'name' => 'Test Asteroid'
            ]);

        $sampleData = [
            'near_earth_objects' => [
                '2025-01-01' => [
                    [
                        'neo_reference_id' => '123',
                        'name' => 'Large Asteroid',
                        'absolute_magnitude_h' => 15.5,
                        'estimated_diameter' => [
                                'kilometers' => [
                                    'estimated_diameter_min' => 0.0324007435,
                                    'estimated_diameter_max' => 0.0724502651,
                                ],
                                'meters' => [
                                    'estimated_diameter_min' => 32.4007435394,
                                    'estimated_diameter_max' => 72.4502650757,
                                ],
                                'miles' => [
                                    'estimated_diameter_min' => 0.0201328824,
                                    'estimated_diameter_max' => 0.0450184937,
                                ],
                                'feet' => [
                                    'estimated_diameter_min' => 106.3016554339,
                                    'estimated_diameter_max' => 237.6977276709,
                                ],
                            ],
                        'is_potentially_hazardous_asteroid' => false,
                        'close_approach_data' => [
                            [
                                'close_approach_date' => '2025-01-01',
                                'close_approach_date_full' => '2025-Jan-01 10:18',
                                'epoch_date_close_approach' => 1735726680000,
                                'relative_velocity' => [
                                    'kilometers_per_second' => '15.9737833452',
                                    'kilometers_per_hour' => '57505.6200425926',
                                    'miles_per_hour' => '35731.7559647987',
                                ],
                                'miss_distance' => [
                                    'astronomical' => '0.0429341231',
                                    'lunar' => '16.7013738859',
                                    'kilometers' => '6422853.366077797',
                                    'miles' => '3990976.0176637186',
                                ],
                                'orbiting_body' => 'Earth',
                            ],
                        ],
                        'is_sentry_object' => false,
                    ]
                ]
            ]
        ];

        $this->mockApiClient->method('fetchObjects')
            ->willReturn($sampleData);

        $result = $this->service->fetch('2025-01-01', '2025-01-01');

        $this->assertEquals(1, $result['skipped']);
        $this->assertEquals('Test Asteroid', NearEarthObject::first()->name);
    }

    public function test_roll_back_transaction_on_error()
    {
        $this->mockApiClient->method('fetchObjects')
            ->willThrowException(new Exception('API error'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to fetch and store NEO Entries: API error');

        $this->service->fetch('2025-01-01', '2025-01-01');

        $this->assertEquals(0, NearEarthObject::count());
    }

    public function test_analysis_calculations()
    {
        $neo1 = NearEarthObject::factory()->create([
            'ref_id' => '123',
            'name' => 'Asteroid 1',
            'date' => '2025-01-01',
            'estimated_diameter_min' => 10,
            'estimated_diameter_max' => 20,
            'is_hazardous' => true,
            'abs_magnitude' => 15,
            'velocity' => 1000,
            'miss_distance' => 500,
        ]);

        $neo2 = NearEarthObject::factory()->create([
            'ref_id' => '456',
            'name' => 'Asteroid 2',
            'date' => '2025-01-01',
            'estimated_diameter_min' => 20,
            'estimated_diameter_max' => 40,
            'is_hazardous' => false,
            'abs_magnitude' => 16,
            'velocity' => 2000,
            'miss_distance' => 300,
        ]);

        $result = $this->service->analyse('2025-01-01', '2025-01-01');

        $analysis = Analysis::first();
        $this->assertEquals(2, $analysis->total_count);
        $this->assertEquals(15, $analysis->avg_estimated_diameter_min);
        $this->assertEquals(30, $analysis->avg_estimated_diameter_max);
        $this->assertEquals(2000, $analysis->max_velocity);
        $this->assertEquals(300, $analysis->smallest_miss_distance);
        $this->assertTrue($analysis->nearEarthObjects->contains($neo1));
    }

    public function test_skip_dates_without_entries()
    {
        $result = $this->service->analyse('2025-01-01', '2025-01-03');
        $this->assertEquals(0, Analysis::count());
    }

    public function test_handle_empty_api_response()
    {
        $this->mockApiClient->method('fetchObjects')
            ->willReturn(['near_earth_objects' => []]);

        $result = $this->service->fetch('2025-01-01', '2025-01-01');

        $this->assertEquals(0, $result['fetched']);
        $this->assertEquals(0, NearEarthObject::count());
    }
}
