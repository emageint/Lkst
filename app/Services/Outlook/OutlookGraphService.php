<?php

namespace App\Services\Outlook;

use App\Models\ExternalCalendarAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class OutlookGraphService
{
    public function getValidAccessToken(ExternalCalendarAccount $account): string
    {
//        Log::debug('Outlook token check', [
//            'token_expires_at' => $account->token_expires_at?->toIso8601String(),
//            'now' => Carbon::now()->toIso8601String(),
//            'is_future' => $account->token_expires_at?->isFuture(),
//        ]);

        if ($account->token_expires_at && $account->token_expires_at->isFuture()) {
            return $account->access_token;
        }


        $tenantId = config('services.outlook.tenant_id') ?: env('OUTLOOK_TENANT_ID', 'common');
        $clientId = config('services.outlook.client_id') ?: env('OUTLOOK_CLIENT_ID');
        $clientSecret = config('services.outlook.client_secret') ?: env('OUTLOOK_CLIENT_SECRET');

        $response = Http::asForm()->post(
            "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
            [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $account->refresh_token,
                'scope' => 'offline_access Calendars.ReadWrite',
            ]
        );

        $payload = $response->json();
//        Log::debug('Outlook token refresh response', [
//            'status' => $response->status(),
//            'error' => $payload['error'] ?? null,
//            'error_description' => $payload['error_description'] ?? null,
//            'has_access_token' => isset($payload['access_token']),
//        ]);
        $newAccessToken = $payload['access_token'] ?? $account->access_token;

        $account->update([
            'access_token' => $newAccessToken,
            'refresh_token' => $payload['refresh_token'] ?? $account->refresh_token,
            'token_expires_at' => isset($payload['expires_in'])
                ? Carbon::now()->addSeconds((int)$payload['expires_in'])
                : $account->token_expires_at,
        ]);

        return $newAccessToken;
    }


    public function createEvent(ExternalCalendarAccount $account, array $payload): array
    {
        $token = $this->getValidAccessToken($account);

        $response = Http::withToken($token)
            ->post('https://graph.microsoft.com/v1.0/me/events', $payload);

//        Log::debug('Outlook createEvent response', [
//            'status' => $response->status(),
//            'body' => $response->body(),
//        ]);

        return $response->json() ?? [];
    }


    public function updateEvent(ExternalCalendarAccount $account, string $eventId, array $payload): void
    {
        $token = $this->getValidAccessToken($account);

        Http::withToken($token)
            ->patch("https://graph.microsoft.com/v1.0/me/events/{$eventId}", $payload);
    }

    public function deleteEvent(ExternalCalendarAccount $account, string $eventId): void
    {
        $token = $this->getValidAccessToken($account);

        Http::withToken($token)
            ->delete("https://graph.microsoft.com/v1.0/me/events/{$eventId}");
    }
}


