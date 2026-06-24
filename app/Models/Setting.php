<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }
}
