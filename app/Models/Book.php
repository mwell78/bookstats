<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'subtitle',
        'author',
        'isbn',
        'pages',
        'format',
        'status',
        'started_at',
        'finished_at',
        'cover_image',
        'notes',
        'published_year',
        'genre',
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }
}
