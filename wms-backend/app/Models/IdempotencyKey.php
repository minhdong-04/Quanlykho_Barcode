<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $table = 'idempotency_keys';
    protected $fillable = ['key', 'user_id', 'request_method', 'request_path', 'request_hash', 'response_data'];
    protected $casts = [
        'response_data' => 'array'
    ];
}
