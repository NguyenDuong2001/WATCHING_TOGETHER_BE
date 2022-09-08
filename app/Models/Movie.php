<?php

namespace App\Models;

use App\Enums\MovieStatus;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movie extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'view',
        'year',
        'status',
        'company',
        'is_series',
        'country_id',
        'director_id',
        'description',
        'movie_duration',
        'publication_time',
    ];

    protected $hidden = [
        'media',
        'country_id',
        'director_id',
    ];

    protected $appends = [
        'poster',
        'video',
        'actor',
        'country',
        'traller',
        'director',
        'category',
        'thumbnail',
        // 'IMDb'
    ];

    public function getPosterAttribute()
    {
        $listPosters = collect([]);

        $posters = $this->getMedia('poster');
        if ($posters->count() <= 0){
            return [
                "https://picsum.photos/1920/1080", 
                "https://picsum.photos/1920/1080"
            ];
        }
        
        foreach ($posters as $poster){
            $listPosters->push($poster->getFullUrl());
        }

        return $listPosters;
    }

    public function getVideoAttribute()
    {
        $listVideos = collect([]);

        $videos = $this->getMedia('video');
        if ($videos->count()){
            foreach ($videos as $video){
                $listVideos->push($video->getFullUrl());
            }
        }

        return $listVideos;
    }

    public function getTrallerAttribute()
    {
        $listTrallers = collect([]);

        $trallers = $this->getMedia('traller');
        if ($trallers->count()){
            foreach ($trallers as $traller){
                $listTrallers->push($traller->getFullUrl());
            }
        }

        return $listTrallers;
    }

    public function getThumbnailAttribute()
    {
        $listThumbnails = collect([]);

        $thumbnails = $this->getMedia('thumbnail');
        if ($thumbnails->count() <= 0){
            return ["https://picsum.photos/500/400"];
        }

        foreach ($thumbnails as $thumbnail){
            $listThumbnails->push($thumbnail->getFullUrl());
        }

        return $listThumbnails;
    }

    public function getIMDbAttribute(){
        $rate = $this->rates()->get();
        if (!$rate->count()){
            return 0;
        }

        $sum = $this->rates()->sum('rate');

        return $sum / $rate->count() * 2;
    }

    public function getCountryAttribute()
    {
        return $this->country()->first();
    }

    public function getDirectorAttribute()
    {
        return $this->director()->first();
    }

    public function getActorAttribute()
    {
        return $this->actors()->get();
    }

    public function getCategoryAttribute()
    {
        return $this->categories()->get();
    }

    public function actors()
    {
        return $this->belongsToMany(Actor::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function director()
    {
        return $this->belongsTo(Director::class);
    }

    public function rates(){
        return $this->hasMany(Rate::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('poster');
        $this->addMediaCollection('video');
        $this->addMediaCollection('traller');
        $this->addMediaCollection('thumbnail')->singleFile();
    }

    public static function options($option, $limit = 20, $category, $country)
    {
        $query = Movie::where(function($query) use ($category, $country) {
            if ($category){
                $query->whereHas('categories', function($q) use ($category) {
                    $q->where('categories.id','=', $category);
                });
            }

            if ($country){
                    $query->where('country_id', $country);
            }

            return $query;
        });

        if($option == 'new'){
            return $query->orderBy('publication_time', 'desc')->take($limit)->get();
        }

        if($option == 'popular'){
            return $query->orderBy('view', 'desc')->take($limit)->get();
        }

        if($option == 'trending'){  
            return $query->get()->sortByDesc('IMDb')->values()->slice(0,$limit);
        }

        $movies = collect([]);

        $most_view = $query->orderBy('view', 'desc')->first();
        $movies->push($most_view);

        $most_publication_time = $query->whereNotIn('id', [$most_view->id])->orderBy('publication_time', 'desc')->first();
        $movies->push($most_publication_time);

        $movies->push($query->whereNotIn('id', [$most_view->id, $most_publication_time->id])->get()->sortByDesc('IMDb')->first());

        return $movies;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('Published', function (Builder $builder) {
            $builder->where('status', MovieStatus::Published);
        });
    }
}
