<?php

namespace App\Http\Controllers;

use App\Services\Outlook\OutlookCalendarService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OutlookWebhookController extends Controller
{
    public function __invoke(Request $request, OutlookCalendarService $service): Response
    {
        if ($request->query('validationToken')) {
            return new Response($request->query('validationToken'), 200, [
                'Content-Type' => 'text/plain',
            ]);
        }

        if (! $this->isValidClientState($request)) {
            return new Response('', 403);
        }

        $notifications = $request->input('value', []);
        if (! empty($notifications)) {
            $service->pullFromOutlook();
        }

        return new Response('', 202);
    }

    private function isValidClientState(Request $request): bool
    {
        $notifications = $request->input('value', []);
        $secret = config('services.outlook.webhook_secret');

        foreach ($notifications as $notification) {
            if (($notification['clientState'] ?? null) !== $secret) {
                return false;
            }
        }

        return true;
    }
}
