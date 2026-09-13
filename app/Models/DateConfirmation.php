<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DateConfirmation extends Model
{
    protected $fillable = [
        'recipient_phone',
        'confirmation_date',
        'confirmation_time',
        'chosen_option',
        'status',
        'whatsapp_message_id',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'confirmation_date' => 'date',
        ];
    }
}
