<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Google\Auth\Credentials\ServiceAccountCredentials;

class BigQueryService
{
    protected string $projectId;
    protected string $dataset;
    protected string $credentials;
    protected string $location;

    public function __construct()
    {
        $this->projectId   = config('services.bigquery.project_id');
        $this->dataset     = config('services.bigquery.dataset');
        $this->credentials = config('services.bigquery.credentials');
        $this->location    = config('services.bigquery.location',
                                'asia-southeast1');
    }

    /**
     * Run any BigQuery SQL query
     */
    public function runQuery(string $sql): array
    {
        try {
            $token    = $this->getAccessToken();
            $endpoint = "https://bigquery.googleapis.com"
                      . "/bigquery/v2/projects/"
                      . "{$this->projectId}/queries";

            $response = Http::withToken($token)
                ->post($endpoint, [
                    'query'        => $sql,
                    'useLegacySql' => false,
                    'location'     => $this->location,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data'    => $response->json()['rows'] ?? [],
                ];
            }

            return [
                'success' => false,
                'message' => 'Query failed',
            ];

        } catch (\Exception $e) {
            Log::error('BigQuery error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get top viewed establishments
     * from GA4 events in BigQuery
     */
    public function getTopEstablishments(
        int $limit = 10
    ): array {
        $sql = "
            SELECT
                ep.value.string_value
                    AS establishment_name,
                COUNT(*) AS view_count
            FROM
                `{$this->dataset}.events_*`,
                UNNEST(event_params) AS ep
            WHERE
                event_name = 'establishment_viewed'
                AND ep.key = 'establishment_name'
                AND _TABLE_SUFFIX >= FORMAT_DATE(
                    '%Y%m%d',
                    DATE_SUB(CURRENT_DATE(),
                    INTERVAL 30 DAY)
                )
            GROUP BY establishment_name
            ORDER BY view_count DESC
            LIMIT {$limit}
        ";

        return $this->runQuery($sql);
    }

    /**
     * Get Coffee Trail analytics
     * Shows most popular variety preferences
     */
    public function getCoffeeTrailAnalytics(): array
    {
        $sql = "
            SELECT
                ep.value.string_value
                    AS variety_preference,
                COUNT(*) AS trail_count,
                AVG(CAST(
                    (SELECT value.int_value
                     FROM UNNEST(event_params)
                     WHERE key = 'number_of_stops')
                AS INT64)) AS avg_stops
            FROM
                `{$this->dataset}.events_*`,
                UNNEST(event_params) AS ep
            WHERE
                event_name = 'coffee_trail_generated'
                AND ep.key = 'variety_preference'
                AND _TABLE_SUFFIX >= FORMAT_DATE(
                    '%Y%m%d',
                    DATE_SUB(CURRENT_DATE(),
                    INTERVAL 30 DAY)
                )
            GROUP BY variety_preference
            ORDER BY trail_count DESC
        ";

        return $this->runQuery($sql);
    }

    /**
     * Get marketplace sales analytics
     * Shows revenue by coffee variety
     */
    public function getSalesAnalytics(): array
    {
        $sql = "
            SELECT
                ep.value.string_value AS variety,
                COUNT(*) AS order_count,
                SUM(CAST(
                    (SELECT value.double_value
                     FROM UNNEST(event_params)
                     WHERE key = 'value')
                AS FLOAT64)) AS total_revenue
            FROM
                `{$this->dataset}.events_*`,
                UNNEST(event_params) AS ep
            WHERE
                event_name = 'purchase'
                AND ep.key = 'variety'
                AND _TABLE_SUFFIX >= FORMAT_DATE(
                    '%Y%m%d',
                    DATE_SUB(CURRENT_DATE(),
                    INTERVAL 30 DAY)
                )
            GROUP BY variety
            ORDER BY total_revenue DESC
        ";

        return $this->runQuery($sql);
    }

    /**
     * Get review analytics
     * Shows average ratings per establishment
     */
    public function getReviewAnalytics(): array
    {
        $sql = "
            SELECT
                ep.value.string_value
                    AS establishment_name,
                COUNT(*) AS review_count,
                AVG(CAST(
                    (SELECT value.double_value
                     FROM UNNEST(event_params)
                     WHERE key = 'overall_rating')
                AS FLOAT64)) AS avg_rating
            FROM
                `{$this->dataset}.events_*`,
                UNNEST(event_params) AS ep
            WHERE
                event_name = 'review_submitted'
                AND ep.key = 'establishment'
                AND _TABLE_SUFFIX >= FORMAT_DATE(
                    '%Y%m%d',
                    DATE_SUB(CURRENT_DATE(),
                    INTERVAL 30 DAY)
                )
            GROUP BY establishment_name
            ORDER BY avg_rating DESC
        ";

        return $this->runQuery($sql);
    }

    /**
     * Get user role distribution
     * Shows how many of each role are active
     */
    public function getUserRoleAnalytics(): array
    {
        $sql = "
            SELECT
                ep.value.string_value AS user_role,
                COUNT(DISTINCT user_pseudo_id)
                    AS unique_users
            FROM
                `{$this->dataset}.events_*`,
                UNNEST(event_params) AS ep
            WHERE
                event_name = 'login'
                AND ep.key = 'method'
                AND _TABLE_SUFFIX >= FORMAT_DATE(
                    '%Y%m%d',
                    DATE_SUB(CURRENT_DATE(),
                    INTERVAL 30 DAY)
                )
            GROUP BY user_role
            ORDER BY unique_users DESC
        ";

        return $this->runQuery($sql);
    }

    /**
     * Get BigQuery access token
     */
    private function getAccessToken(): string
    {
        return Cache::remember('bigquery_token', 3500,
            function () {
                $credentials = new ServiceAccountCredentials(
                    ['https://www.googleapis.com/auth/bigquery'],
                    $this->credentials
                );

                $token = $credentials->fetchAuthToken();
                return $token['access_token'];
            }
        );
    }
}