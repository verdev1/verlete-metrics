<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'client_id',
        'type',
        'reporting_month',
        'recipient_email',
        'subject',
        'body',
        'status',
        'error_message',
        'attempted_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'reporting_month' => 'date',
            'attempted_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}