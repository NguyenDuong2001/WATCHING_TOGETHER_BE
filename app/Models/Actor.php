<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Actor extends Model implements HasMedia
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
        'pivot',
    ];

    protected $appends = [
        'avatar',
        'country'
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

    public function movies()
    {
        return $this->belongsToMany(Movie::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function getCountryAttribute()
    {
        return $this->country()->first();
    }
}
