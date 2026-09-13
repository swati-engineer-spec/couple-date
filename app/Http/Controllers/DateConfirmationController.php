<?php

namespace App\Http\Controllers;

use App\Models\DateConfirmation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DateConfirmationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string', 'max:40'],
            'option' => ['required', 'string', 'in:Pizza,Sushi,Burgers,Pasta,Tacos,Ramen'],
        ]);

        $recipientPhone = config('services.whatsapp.recipient_phone');
        if (! $recipientPhone) {
            return response()->json(['message' => 'The WhatsApp recipient is not configured.'], 503);
        }

        $confirmation = DateConfirmation::create([
            'recipient_phone' => $recipientPhone,
            'confirmation_date' => $data['date'],
            'confirmation_time' => $data['time'],
            'chosen_option' => $data['option'],
            'status' => 'pending',
        ]);

        if (! config('services.whatsapp.access_token') || ! config('services.whatsapp.phone_number_id')) {
            $confirmation->update([
                'status' => 'not_configured',
                'error_message' => 'WhatsApp Cloud API credentials are not configured.',
            ]);

            return response()->json([
                'message' => 'Your date was saved. WhatsApp sending is not configured yet.',
                'confirmation_id' => $confirmation->id,
            ], 202);
        }

        $message = "It's a date! ♥\n\nDate: {$confirmation->confirmation_date->format('l, F j, Y')}\nTime: {$confirmation->confirmation_time}\nDinner vibe: {$confirmation->chosen_option}\n\nI cannot wait to see you.";
        $version = config('services.whatsapp.api_version', 'v21.0');
        $url = sprintf(
            'https://graph.facebook.com/%s/%s/messages',
            $version,
            config('services.whatsapp.phone_number_id')
        );

        try {
            $response = Http::withToken(config('services.whatsapp.access_token'))
                ->acceptJson()
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $confirmation->recipient_phone,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message],
                ]);

            if ($response->successful()) {
                $confirmation->update([
                    'status' => 'sent',
                    'whatsapp_message_id' => data_get($response->json(), 'messages.0.id'),
                ]);

                return response()->json([
                    'message' => 'Date confirmation sent on WhatsApp.',
                    'confirmation_id' => $confirmation->id,
                ]);
            }

            $confirmation->update([
                'status' => 'failed',
                'error_message' => $response->body(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('WhatsApp date confirmation failed.', [
                'confirmation_id' => $confirmation->id,
                'exception' => $exception,
            ]);
            $confirmation->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);
        }

        return response()->json([
            'message' => 'Your date was saved, but WhatsApp could not be sent.',
            'confirmation_id' => $confirmation->id,
        ], 502);
    }
}
