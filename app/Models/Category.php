<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description'
    ];

    protected $hidden=[
        'pivot'
    ];

    protected $appends = [
        'movie_count'
    ];

    public function getMovieCountAttribute()
    {
        return $this->movies()->count();
    }

    public function movies()
    {
        return $this->belongsToMany(Movie::class);
    }
}
