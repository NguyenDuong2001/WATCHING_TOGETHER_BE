<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Movie extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'movie_duration',
        'publication_time',
        'view',
        'description',
        'is_series',
        'status',
        'year',
        'country_id',
        'director_id',
        'company'
    ];

    public function actors()
    {
        return $this->belongsToMany(Actor::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('poster');
        $this->addMediaCollection('video');
        $this->addMediaCollection('traller');
        $this->addMediaCollection('thumbnail')->singleFile();
    }
}
