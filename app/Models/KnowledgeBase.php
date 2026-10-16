<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $fillable = [
        'title',
        'question',
        'answer',
        'category',
        'keywords',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
