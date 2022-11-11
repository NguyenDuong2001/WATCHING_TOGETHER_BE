<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Director extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'country_id',
        'description',
        'date_of_birth',
    ];

    protected $hidden=[
        'media',
        'country_id'
    ];

    protected $appends = [
        'avatar',
        'country',
        'movie_joined'
    ];

    public function getAvatarAttribute()
    {
        $listAvatars = collect([]);

        $avatars = $this->getMedia('avatar');

        if ($avatars->count() <= 0){
            return ['https://i.pravatar.cc/300?img='.rand(1, 70)];
        }

        foreach ($avatars as $avatar){
            $listAvatars->push($avatar->getFullUrl());
        }

        return $listAvatars;
    }

    public function getCountryAttribute()
    {
        return $this->country()->first();
    }

    public function getMovieJoinedAttribute()
    {
        return $this->movies()->count();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function movies(){
        return $this->hasMany(Movie::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

}
