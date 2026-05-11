<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'method',
        'endpoint',
        'request_headers',
        'request_body',
        'response_code',
        'response_body',
        'api_key_used',
        'ip_address',
        'duration_ms',
        'reference_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'request_headers' => 'array',
            'request_body'    => 'array',
            'response_body'   => 'array',
            'response_code'   => 'integer',
            'duration_ms'     => 'integer',
            'created_at'      => 'datetime',
        ];
    }
}
