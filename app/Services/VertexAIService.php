<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Google\Auth\Credentials\ServiceAccountCredentials;

class VertexAIService
{
    protected string $project;
    protected string $location;
    protected string $credentials;

    public function __construct()
    {
        $this->project     = config('services.vertex_ai.project');
        $this->location    = config('services.vertex_ai.location');
        $this->credentials = config('services.vertex_ai.credentials');
    }

    /**
     * Analyze sentiment of a review
     * Uses Google Natural Language API
     * Pre-built model — no training needed ✅
     */
    public function analyzeSentiment(
        string $reviewText
    ): array {
        try {
            $token    = $this->getAccessToken();
            $endpoint = "https://language.googleapis.com"
                      . "/v1/documents:analyzeSentiment";

            $response = Http::withToken($token)
                ->post($endpoint, [
                    'document' => [
                        'type'    => 'PLAIN_TEXT',
                        'content' => $reviewText,
                    ],
                    'encodingType' => 'UTF8',
                ]);

            if ($response->successful()) {
                $data  = $response->json();
                $score = $data['documentSentiment']['score'];

                // Convert score to label
                // Score: -1.0 (negative) to 1.0 (positive)
                if ($score >= 0.25) {
                    $sentiment = 'positive';
                } elseif ($score <= -0.25) {
                    $sentiment = 'negative';
                } else {
                    $sentiment = 'neutral';
                }

                return [
                    'success'   => true,
                    'sentiment' => $sentiment,
                    'score'     => $score,
                    'magnitude' => $data['documentSentiment']['magnitude'],
                ];
            }

            return [
                'success' => false,
                'message' => 'Sentiment analysis failed',
            ];

        } catch (\Exception $e) {
            Log::error('Vertex AI: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get cached Google Cloud access token
     */
    private function getAccessToken(): string
    {
        return Cache::remember('gcloud_token', 3500,
            function () {
                $credentials = new ServiceAccountCredentials(
                    [
                        'https://www.googleapis.com/auth/cloud-platform',
                        'https://www.googleapis.com/auth/cloud-language',
                    ],
                    $this->credentials
                );

                $token = $credentials->fetchAuthToken();
                return $token['access_token'];
            }
        );
    }
}