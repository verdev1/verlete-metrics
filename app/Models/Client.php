<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'website',
    'analytics_property',
    'store',
    'application_username',
    'application_password',
    'surecart_api_key',
    'recipient_names',
    'emails',
    'message',
])]
class Client extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'application_password' => 'encrypted',
            'surecart_api_key' => 'encrypted',
        ];
    }
}