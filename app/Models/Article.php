<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title',
        'description',
        'url',
        'image_url',
        'source',
        'category',
        'published_at'
    ];

    protected array $dates = ['published_at'];
}
