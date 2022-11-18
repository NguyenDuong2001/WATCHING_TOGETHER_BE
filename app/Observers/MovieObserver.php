<?php

namespace App\Observers;

use App\Enums\MovieStatus;
use App\Enums\ReviewStatus;
use App\Models\Movie;

class MovieObserver
{
    /**
     * Handle the Movie "created" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function created(Movie $movie)
    {
        //
    }

    /**
     * Handle the Movie "saving" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function saving(Movie $movie)
    {
        if ($movie->reviews()->count() === 0 || $movie->status != MovieStatus::Archived) {
            return;
        }

        $movie->reviews->each(function($review) {
            $review->status = ReviewStatus::Canceled;
            $review->save();
        });
    }

    /**
     * Handle the Movie "deleted" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function deleted(Movie $movie)
    {
        //
    }

    /**
     * Handle the Movie "restored" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function restored(Movie $movie)
    {
        //
    }

    /**
     * Handle the Movie "force deleted" event.
     *
     * @param  \App\Models\Movie  $movie
     * @return void
     */
    public function forceDeleted(Movie $movie)
    {
        //
    }
}
