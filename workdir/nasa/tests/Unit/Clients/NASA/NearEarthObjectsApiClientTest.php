<?php

namespace Tests\Unit\Clients\NASA;

use App\Clients\NASA\NearEarthObjectsApiClient;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class NearEarthObjectsApiClientTest extends TestCase
{
    private $httpClient;
    private NearEarthObjectsApiClient $apiClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(Client::class);
        $this->apiClient = new NearEarthObjectsApiClient($this->httpClient);

        config(['services.nasa_neo_api.key' => 'test_api_key']);
    }

    public function test_fetch_objects_with_invalid_date_range()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Date range exceeds maximum allowed (7 days)');

        $this->apiClient->fetchObjects('2025-01-01', '2025-01-09');
    }

    public function test_throws_exception_if_api_key_not_configured()
    {
        config(['services.nasa_neo_api.key' => null]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('NASA API key is not configured');

        new NearEarthObjectsApiClient($this->createMock(Client::class));
    }

    public function test_throws_exception_if_api_request_fails()
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willThrowException(new Exception('API error'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected error: API error');

        $this->apiClient->fetchObjects('2025-01-01', '2025-01-07');
    }

    public function test_throws_exception_if_response_is_not_valid_json()
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->willReturn(
                new Response(200, [], 'invalid-json')
            );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid request: json_decode error: Syntax error');

        $this->apiClient->fetchObjects('2025-01-01', '2025-01-07');
    }

}
