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
    'emails',
    'message',
])]
class Client extends Model
{
    use HasFactory;
}