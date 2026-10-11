<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlutterwaveWebhookController extends Controller
{
    /**
     * Handle Flutterwave webhook (e.g. charge.completed for bank transfer).
     * Stores the payment as a subscription when status indicates success.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        // Flutterwave sends event type: charge.completed, etc.
        $event = $payload['event'] ?? $payload['type'] ?? null;
        if ($event !== 'charge.completed') {
            return response()->json(['status' => 'ignored', 'message' => 'Event not handled'], 200);
        }

        $data = $payload['data'] ?? [];
        $status = $data['status'] ?? '';
        $txRef = $data['tx_ref'] ?? $data['reference'] ?? $data['id'] ?? null;
        $amount = (float) ($data['amount'] ?? 0);
        $customer = $data['customer'] ?? [];

        // Accept both "successful" and "succeeded" (different Flutterwave versions)
        if (! in_array(strtolower($status), ['successful', 'succeeded'], true)) {
            return response()->json(['status' => 'ignored', 'message' => 'Transaction not successful'], 200);
        }

        if (! $txRef || $amount <= 0) {
            Log::warning('Flutterwave webhook missing tx_ref or amount', ['data' => $data]);
            return response()->json(['status' => 'error', 'message' => 'Missing tx_ref or amount'], 400);
        }

        // Resolve user_id and optional due/registration from meta (Flutterwave may send meta as object or as array of {metaname, metavalue})
        $meta = $this->normalizeMeta($data['meta'] ?? []);
        $userId = $meta['user_id'] ?? null;
        if (! $userId && ! empty($customer['email'])) {
            $user = User::where('email', $customer['email'])->first();
            $userId = $user?->id;
        }

        if (! $userId) {
            Log::warning('Flutterwave webhook could not resolve user_id', ['tx_ref' => $txRef, 'data' => $data]);
            return response()->json(['status' => 'ignored', 'message' => 'Could not resolve user'], 200);
        }

        $subscriptionPayload = [
            'user_id' => $userId,
            'amount' => $amount,
            'status' => 'Successful',
        ];

        // due and registration from meta (frontend sends these when creating the payment)
        if (isset($meta['due'])) {
            $subscriptionPayload['due'] = (float) $meta['due'];
        }
        if (isset($meta['registration'])) {
            $subscriptionPayload['registration'] = (float) $meta['registration'];
        }

        Subscription::updateOrCreate(
            ['tx_ref' => $txRef],
            $subscriptionPayload
        );

        return response()->json(['status' => 'ok', 'message' => 'Subscription stored'], 200);
    }

    /**
     * Normalize Flutterwave meta: can be associative array or list of {metaname, metavalue}.
     */
    private function normalizeMeta($meta): array
    {
        if (! is_array($meta)) {
            return [];
        }
        if (isset($meta['user_id']) || isset($meta['due']) || isset($meta['registration'])) {
            return $meta;
        }
        $out = [];
        foreach ($meta as $item) {
            if (is_array($item) && isset($item['metaname'], $item['metavalue'])) {
                $out[$item['metaname']] = $item['metavalue'];
            }
        }
        return $out;
    }
}
