<?php

namespace App\Http\Controllers;

use App\Models\ExternalCalendarAccount;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;


class OutlookOAuthController extends Controller
{
    public function connect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('outlook_oauth_state', $state);


        $tenantId = config('services.outlook.tenant_id') ?: env('OUTLOOK_TENANT_ID', 'common');
        $clientId = config('services.outlook.client_id') ?: env('OUTLOOK_CLIENT_ID');
        $redirectUri = config('services.outlook.redirect_uri') ?: env('OUTLOOK_REDIRECT_URI');

        if (blank($clientId) || blank($redirectUri)) {
            abort(500, 'Outlook OAuth is missing client_id or redirect_uri.');
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'response_mode' => 'query',
            'scope' => 'offline_access Calendars.ReadWrite',
            'state' => $state,
        ]);
        $url = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?{$query}";

        return redirect()->away($url);
    }


    public function callback(Request $request): RedirectResponse
    {
        $clientId = config('services.outlook.client_id') ?: env('OUTLOOK_CLIENT_ID');
        $clientSecret = config('services.outlook.client_secret') ?: env('OUTLOOK_CLIENT_SECRET');
        $redirectUri = config('services.outlook.redirect_uri') ?: env('OUTLOOK_REDIRECT_URI');

        if (blank($clientId) || blank($clientSecret) || blank($redirectUri)) {
            abort(500, 'Outlook OAuth is missing client credentials or redirect_uri.');
        }

        $state = $request->input('state');
        $expectedState = $request->session()->pull('outlook_oauth_state');

        if (!$state || $state !== $expectedState) {
            abort(403);
        }

        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Outlook authorisation failed.');
        }

        $tenantId = config('services.outlook.tenant_id') ?: env('OUTLOOK_TENANT_ID', 'common');
        $tokenResponse = Http::asForm()->post(
            "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
            [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]
        );

        if (!$tokenResponse->successful()) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Outlook token exchange failed.');
        }

        $tokenData = $tokenResponse->json();
        $accessToken = $tokenData['access_token'] ?? null;
        $refreshToken = $tokenData['refresh_token'] ?? null;
        $expiresIn = (int)($tokenData['expires_in'] ?? 0);

        $calendarId = null;
        if ($accessToken) {
            $calendarResponse = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me/calendars', [
                    '$top' => 1,
                ]);

            if ($calendarResponse->successful()) {
                $calendarId = $calendarResponse->json('value.0.id');
            }
        }

        ExternalCalendarAccount::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'provider' => 'outlook',
            ],
            [
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_expires_at' => $expiresIn > 0 ? Carbon::now()->addSeconds($expiresIn) : null,
                'calendar_id' => $calendarId,
            ]
        );

        return redirect()->route('filament.admin.pages.dashboard')
            ->with('success', 'Outlook calendar connected.');
    }
}
