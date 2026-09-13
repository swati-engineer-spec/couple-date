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
        Log::info('DateConfirmationController@store: Incoming request received.', [
            'date' => $request->input('date'),
            'time' => $request->input('time'),
            'option' => $request->input('option'),
        ]);

        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'string', 'max:40'],
            'option' => ['required', 'string', 'in:Pizza,Sushi,Burgers,Pasta,Tacos,Ramen'],
        ]);

        $recipientPhone = config('services.whatsapp.recipient_phone');

        Log::info('DateConfirmationController: Creating database confirmation entry.', [
            'has_phone' => !empty($recipientPhone),
        ]);

        $confirmation = DateConfirmation::create([
            'recipient_phone' => $recipientPhone ?: 'not-configured',
            'confirmation_date' => $data['date'],
            'confirmation_time' => $data['time'],
            'chosen_option' => $data['option'],
            'status' => 'pending',
        ]);

        $accessToken = config('services.whatsapp.access_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');

        // Check if configuration parameters are missing
        if (! $recipientPhone || ! $accessToken || ! $phoneNumberId) {
            Log::warning('DateConfirmationController: Blocked execution due to missing WhatsApp settings.', [
                'confirmation_id' => $confirmation->id,
                'missing_recipient_phone' => empty($recipientPhone),
                'missing_access_token' => empty($accessToken),
                'missing_phone_number_id' => empty($phoneNumberId),
            ]);

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
            $phoneNumberId
        );

        try {
            Log::info('DateConfirmationController: Dispatching API request to WhatsApp Cloud endpoint.', [
                'confirmation_id' => $confirmation->id,
                'target_url' => $url,
                'to' => $confirmation->recipient_phone,
            ]);

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'to' => $confirmation->recipient_phone,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $message],
                ]);

            if ($response->successful()) {
                $whatsappMsgId = data_get($response->json(), 'messages.0.id');
                
                Log::info('DateConfirmationController: WhatsApp delivery successful.', [
                    'confirmation_id' => $confirmation->id,
                    'whatsapp_message_id' => $whatsappMsgId,
                ]);

                $confirmation->update([
                    'status' => 'sent',
                    'whatsapp_message_id' => $whatsappMsgId,
                ]);

                return response()->json([
                    'message' => 'Date confirmation sent on WhatsApp.',
                    'confirmation_id' => $confirmation->id,
                ]);
            }

            // Handles API response errors (e.g., HTTP 400 or 401)
            Log::error('DateConfirmationController: WhatsApp API responded with an error code.', [
                'confirmation_id' => $confirmation->id,
                'status_code' => $response->status(),
                'response_body' => $response->body(),
            ]);

            $confirmation->update([
                'status' => 'failed',
                'error_message' => $response->body(),
            ]);
        } catch (\Throwable $exception) {
            // Handles system or network exceptions (e.g., DNS timeout, host unreachable)
            Log::critical('DateConfirmationController: Connection exception thrown during WhatsApp delivery.', [
                'confirmation_id' => $confirmation->id,
                'exception_message' => $exception->getMessage(),
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
