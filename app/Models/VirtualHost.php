<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualHost extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_name',
        'document_root',
        'ssl_enabled',
        'port',
        'notes',
        'github_url',
    ];

    protected function casts(): array
    {
        return [
            'ssl_enabled' => 'boolean',
            'port' => 'integer',
        ];
    }
}
