<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DateProgress extends Model
{
    protected $table = 'date_progress';

    protected $fillable = [
        'session_id',
        'last_step',
        'last_step_name',
        'steps_completed',
        'chosen_date',
        'chosen_time',
        'chosen_option',
        'completed',
        'abandoned',
    ];

    protected function casts(): array
    {
        return [
            'steps_completed' => 'array',
            'chosen_date' => 'date',
            'completed' => 'boolean',
            'abandoned' => 'boolean',
        ];
    }
}
