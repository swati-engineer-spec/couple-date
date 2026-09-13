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
        $shouldNotify = $stepChanged && config('services.notifications.email');
        $notificationStatus = 'not_configured';
        if ($shouldNotify) {
            try {
                Mail::to(config('services.notifications.email'))->send(new DateProgressSummary($progress));
                $notificationStatus = 'sent';
            } catch (\Throwable $exception) {
                $notificationStatus = 'failed';
                Log::error('Date progress email failed.', [
                    'session_id' => $progress->session_id,
                    'step' => $progress->last_step,
                    'exception' => $exception,
                ]);
            }
        }

        return response()->json([
            'saved' => true,
            'notification' => $notificationStatus,
        ]);
    }
}
