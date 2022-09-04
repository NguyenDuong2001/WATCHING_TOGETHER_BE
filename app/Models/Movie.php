<?php

namespace App\Models;

use App\Enums\MovieStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Builder;

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

    protected $hidden = [
        'country_id',
        'director_id',
        'media'
    ];

    protected $appends = [
        'poster',
        // 'video',
        // 'traller',
        'thumbnail',
        'country',
        'director',
        'actor',
        'category',
        // 'IMDb'
    ];

    public function getPosterAttribute()
    {
        $listPosters = collect([]);

        $posters = $this->getMedia('poster');
        if ($posters->count()){
            foreach ($posters as $poster){
                $listPosters->push($poster->original_url);
            }
        }
        return $listPosters;
    }

    public function getVideoAttribute()
    {
        $listVideos = collect([]);

        $videos = $this->getMedia('video');
        if ($videos->count()){
            foreach ($videos as $video){
                $listVideos->push($video->original_url);
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
                $listTrallers->push($traller->original_url);
            }
        }
        return $listTrallers;
    }

    public function getThumbnailAttribute()
    {
        $listThumbnails = collect([]);

        $thumbnails = $this->getMedia('thumbnail');
        if ($thumbnails->count()){
            foreach ($thumbnails as $thumbnail){
                $listThumbnails->push($thumbnail->original_url);
            }
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

    public static function options($option, $limit = 20)
    {
        if($option == 'new'){
            return Movie::orderBy('publication_time', 'desc')->take($limit)->get();
        }

        if($option == 'popular'){
            return Movie::orderBy('view', 'desc')->take($limit)->get();
        }

        if($option == 'trending'){
            return Movie::all()->sortByDesc('IMDb')->take($limit);
        }

        $movies = collect([]);

        $most_view = Movie::orderBy('view', 'desc')->first();
        $movies->push($most_view);

        $most_publication_time = Movie::whereNotIn('id', [$most_view->id])->orderBy('publication_time', 'desc')->first();
        $movies->push($most_publication_time);

        $movies->push(Movie::whereNotIn('id', [$most_view->id, $most_publication_time->id])->get()->sortByDesc('IMDb')->first());

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
