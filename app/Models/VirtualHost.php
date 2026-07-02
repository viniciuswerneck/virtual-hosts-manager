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
        'active',
        'template',
        'php_version',
        'notes',
        'github_url',
    ];

    protected function casts(): array
    {
        return [
            'ssl_enabled' => 'boolean',
            'active' => 'boolean',
            'port' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
