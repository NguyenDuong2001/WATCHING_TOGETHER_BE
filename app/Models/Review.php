<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Review extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'status',
        'checker_id',
        'author_id',
        'movie_id',
        'description',
    ];

    protected $hidden=[
        'media',
        'checker_id',
        'author_id',
        'movie_id'
    ];

    protected $appends = [
        'thumbnail',
        'author',
//        'checker',
//        'movie',
//        'video',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function getMovieAttribute()
    {
        return $this->movie()->first();
    }

    public function getCheckerAttribute()
    {
        return $this->checker()->first();
    }

    public function getAuthorAttribute()
    {
        return $this->author()->first();
    }

    public function getVideoAttribute()
    {
        $listVideos = collect([]);

        $videos = $this->getMedia('video');
        if ($videos->count() > 0){
            foreach ($videos as $video){
                $listVideos->push($video->getFullUrl());
            }
        }

        return $listVideos;
    }

    public function getThumbnailAttribute()
    {
        $listThumbnails = collect([]);

        $thumbnails = $this->getMedia('thumbnail');
        if ($thumbnails->count() <= 0){
            return ["https://picsum.photos/id/".rand(1,100)."/500/400"];
        }

        foreach ($thumbnails as $thumbnail){
            $listThumbnails->push($thumbnail->getFullUrl());
        }

        return $listThumbnails;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')->singleFile();
        $this->addMediaCollection('thumbnail')->singleFile();
    }
}
