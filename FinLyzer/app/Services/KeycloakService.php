<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class KeycloakService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('keycloak.http_timeout', 10),
            'verify' => true,
        ]);
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code, string $codeVerifier): ?array
    {
        try {
            $response = $this->client->post(config('keycloak.endpoints.token'), [
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => config('keycloak.client_id'),
                    'client_secret' => config('keycloak.client_secret'),
                    'code' => $code,
                    'redirect_uri' => config('keycloak.redirect_uri'),
                    'code_verifier' => $codeVerifier,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'access_token' => $body['access_token'] ?? null,
                'id_token' => $body['id_token'] ?? null,
                'refresh_token' => $body['refresh_token'] ?? null,
                'expires_in' => $body['expires_in'] ?? null,
            ];
        } catch (GuzzleException $e) {
            Log::error('Token exchange failed', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            return null;
        }
    }

    /**
     * Get user info from Keycloak
     */
    public function getUserInfo(string $accessToken): ?array
    {
        try {
            $response = $this->client->get(config('keycloak.endpoints.userinfo'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Failed to get user info', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Decode JWT token without verification (for getting nonce)
     */
    public function decodeToken(string $token): ?array
    {
        try {
            // Get JWKS to verify signature
            $jwks = $this->getJWKS();
            
            if (!$jwks) {
                // If JWKS fetch fails, decode without verification
                $parts = explode('.', $token);
                if (count($parts) !== 3) {
                    return null;
                }
                
                $payload = json_decode(
                    base64_decode(strtr($parts[1], '-_', '+/')),
                    true
                );
                
                return $payload;
            }

            // TODO: Implement proper JWT verification with JWKS
            // For now, decode without full verification
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                true
            );

            return $payload;
        } catch (\Exception $e) {
            Log::error('Token decode error', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get JWKS from Keycloak
     */
    public function getJWKS(): ?array
    {
        try {
            $response = $this->client->get(config('keycloak.endpoints.jwks'));
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Failed to fetch JWKS', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        try {
            $response = $this->client->post(config('keycloak.endpoints.token'), [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'client_id' => config('keycloak.client_id'),
                    'client_secret' => config('keycloak.client_secret'),
                    'refresh_token' => $refreshToken,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return [
                'access_token' => $body['access_token'] ?? null,
                'id_token' => $body['id_token'] ?? null,
                'refresh_token' => $body['refresh_token'] ?? null,
                'expires_in' => $body['expires_in'] ?? null,
            ];
        } catch (GuzzleException $e) {
            Log::error('Token refresh failed', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Validate token
     */
    public function validateToken(string $token): bool
    {
        try {
            $decoded = $this->decodeToken($token);
            if (!$decoded) {
                return false;
            }

            // Check expiration
            if (isset($decoded['exp']) && $decoded['exp'] < time()) {
                return false;
            }

            // Check issuer
            if (isset($decoded['iss']) && $decoded['iss'] !== config('keycloak.issuer')) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Token validation error', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
