<?php

namespace App\Http\Controllers;

use App\Mail\DateProgressSummary;
use App\Models\DateProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DateProgressController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        Log::info('DateProgressController@store: Incoming request received.', [
            'session_id' => $request->input('session_id')
        ]);

        $data = $request->validate([
            'session_id' => ['required', 'uuid'],
            'step' => ['required', 'integer', 'between:1,5'],
            'step_name' => ['required', 'string', 'in:invite,schedule,vibe,ready,success'],
            'steps_completed' => ['array'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'time' => ['nullable', 'string', 'max:40'],
            'option' => ['nullable', 'string', 'in:Pizza,Sushi,Burgers,Pasta,Tacos,Ramen'],
            'completed' => ['boolean'],
            'abandoned' => ['boolean'],
        ]);

        $existing = DateProgress::where('session_id', $data['session_id'])->first();

        if ($existing) {
            Log::info('DateProgressController: Found existing session record.', [
                'session_id' => $data['session_id'],
                'previous_step' => $existing->last_step
            ]);
        } else {
            Log::info('DateProgressController: No existing session found. Creating new record.', [
                'session_id' => $data['session_id']
            ]);
        }

        $progress = DateProgress::updateOrCreate(
            ['session_id' => $data['session_id']],
            [
                'last_step' => $data['step'],
                'last_step_name' => $data['step_name'],
                'steps_completed' => $data['steps_completed'] ?? [],
                'chosen_date' => $data['date'] ?? null,
                'chosen_time' => $data['time'] ?? null,
                'chosen_option' => $data['option'] ?? null,
                'completed' => $data['completed'] ?? false,
                'abandoned' => $data['abandoned'] ?? false,
            ]
        );

        $stepChanged = ! $existing
            || $existing->last_step !== $progress->last_step
            || $existing->completed !== $progress->completed
            || $existing->abandoned !== $progress->abandoned;

        $targetEmail = config('services.notifications.email');
        $shouldNotify = $stepChanged && $targetEmail;
        $notificationStatus = 'not_configured';

        Log::info('DateProgressController: Evaluating notification conditions.', [
            'session_id' => $progress->session_id,
            'step_changed' => $stepChanged,
            'configured_email' => $targetEmail ? 'Present' : 'Missing/Empty',
            'should_notify' => $shouldNotify
        ]);

        if ($shouldNotify) {
            try {
                Log::info('DateProgressController: Attempting to send email.', [
                    'to' => $targetEmail,
                    'session_id' => $progress->session_id
                ]);
                
                Mail::to($targetEmail)->send(new DateProgressSummary($progress));
                $notificationStatus = 'sent';
                
                Log::info('DateProgressController: Email successfully sent.');
            } catch (\Throwable $exception) {
                $notificationStatus = 'failed';
                Log::error('DateProgressController: Date progress email failed execution.', [
                    'session_id' => $progress->session_id,
                    'step' => $progress->last_step,
                    'exception_message' => $exception->getMessage(),
                    'exception' => $exception,
                ]);
            }
        } else {
            if (!$stepChanged) {
                Log::info('DateProgressController: Notification skipped because step data did not change.');
            }
            if (!$targetEmail) {
                Log::warning('DateProgressController: Notification skipped because services.notifications.email config is empty.');
            }
        }

        return response()->json([
            'saved' => true,
            'notification' => $notificationStatus,
        ]);
    }
}
