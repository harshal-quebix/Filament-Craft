<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'created_by'
    ];

    protected $casts = [
        // 'value' => 'json'
    ];
}
