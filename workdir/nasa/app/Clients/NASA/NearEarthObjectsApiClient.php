<?php

namespace App\Clients\NASA;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\ClientInterface as GuzzleClient;
use GuzzleHttp\Utils;
use RuntimeException;

class NearEarthObjectsApiClient
{
    private const string NEO_FEED_URL = 'https://api.nasa.gov/neo/rest/v1/feed';
    protected const MAX_DATE_RANGE_DAYS = 7;

    private GuzzleClient $httpClient;
    private ?string $apiKey;

    public function __construct(GuzzleClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = config('services.nasa_neo_api.key');

        if (!$this->apiKey) {
            throw new RuntimeException('NASA API key is not configured');
        }
    }

    public function fetchObjects($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->diffInDays($end) > self::MAX_DATE_RANGE_DAYS) {
            throw new \InvalidArgumentException(
                sprintf('Date range exceeds maximum allowed (%d days)', self::MAX_DATE_RANGE_DAYS)
            );
        }

        return $this->request('GET', self::NEO_FEED_URL, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'api_key' => $this->getApiKey(),
        ]);
    }

    private function request(string $method, string $url, array $query): array
    {
        try {

            $method = strtoupper($method);
            if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], true)) {
                throw new \InvalidArgumentException('Invalid HTTP method');
            }

            $options = [];
            if ($method === 'GET') {
                $options['query'] = $query;
            } else {
                $options['body'] = json_encode($query);
            }

            $response = $this->httpClient->request($method, $url, $options);
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                throw new \RuntimeException(
                    sprintf('API returned status code %d', $response->getStatusCode())
                );
            }

            $body = $response->getBody()->getContents();
            $data = Utils::jsonDecode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode JSON response: ' . json_last_error_msg());
            }

            return $data;

        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException(
                sprintf('Invalid request: %s', $e->getMessage())
            );
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new \RuntimeException(
                sprintf('API request failed: %s', $e->getMessage())
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf('Unexpected error: %s', $e->getMessage())
            );
        }
    }

    private function getApiKey(): string
    {
        return $this->apiKey;
    }
}
