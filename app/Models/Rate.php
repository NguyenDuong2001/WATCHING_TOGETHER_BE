<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate',
        'user_id',
        'movie_id'
    ];

    protected $appends = [
        'user',
//        'movie'
    ];

    public function getUserAttribute()
    {
        return $this->user()->first();
    }

    public function getMovieAttribute()
    {
        return $this->movie()->first();
    }

    public function user() {
        return $this->belongsTo(User::class)->select('id','name', 'email');
    }

    public function movie() {
        return $this->belongsTo(Movie::class)->select('name', 'publication_time');
    }
}
