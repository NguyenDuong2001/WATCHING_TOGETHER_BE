<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Enums\MovieStatus;
use App\Enums\RoleType;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\Rate;
use App\Models\Reply;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request, $option)
    {
        return response()->json([
            'movies' => Movie::options($option, $request->limit, $request->category, $request->country),
        ], 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Response
     */
    public function index_admin(Request $request, Movie $movie)
    {
        $this->authorize('viewAny', $movie);

        $search = Str::of($request->get('search'))->trim();
        if ($search != "") {
            return response()->json([
                'movies' => Movie::withoutGlobalScope('Published')->select('id', 'name', 'publication_time', 'movie_duration', 'director_id', 'status')->where('name', 'like', "%$search%")->paginate($request->get('limit') ?: 5),
            ]);
        }

        return response()->json([
            'movies' => Movie::withoutGlobalScope('Published')->select('id', 'name', 'publication_time', 'movie_duration', 'director_id', 'status')->paginate($request->get('limit') ?: 5),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request, Movie $movie)
    {
        $this->authorize('create', $movie);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50', 'unique:movies', 'min:2'],
            'description' => ['nullable', 'max:255'],
            'company' => ['nullable', 'max:50'],
            'url_video' => ['nullable', 'url'],
            'limit_age' => ['nullable', 'numeric', 'min:1', 'max:99'],
            'country_id' => ['exists:countries,id', 'nullable'],
            'is_series' => ['nullable', 'boolean'],
            'movie_duration' => ['nullable', 'numeric', 'min:1'],
            'director_id' => ['nullable', 'exists:directors,id'],
            'publication_time' => ['date_format:Y-m-d', 'required'],
            'thumbnail' => ['required', 'mimes:jpeg,jpg,png,gif','max:10000'],
            'poster' => ['required', 'mimes:jpeg,jpg,png,gif','max:10000'],
            'poster_sub' => ['required', 'mimes:jpeg,jpg,png,gif','max:10000'],
            'video' => 'nullable|mimes:mp4,mov,ogg,qt',
            'trailer' => 'nullable|mimes:mp4,mov,ogg,qt',
            'categories' => 'nullable|array',
            'categories.*' => 'numeric|exists:categories,id',
            'actors' => 'nullable|array',
            'actors.*' => 'numeric|exists:actors,id',

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }
        try {
            $movie = Movie::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'publication_time' => $request->input('publication_time'),
                'company' => $request->input('company'),
                'url_video' => $request->input('url_video'),
                'limit_age' => $request->input('limit_age'),
                'is_series' => $request->input('is_series') ?: false,
                'movie_duration' => $request->input('movie_duration'),
                'director_id' => $request->input('director_id'),
                'country_id' => $request->input('country_id'),
            ]);

            if ($request->input('categories')) {
                foreach ($request->input('categories') as $id) {
                    if ($movie->categories()->where('categories.id', $id)->exists()) {
                        continue;
                    }

                    $movie->categories()->attach($id);
                }
            }

            if ($request->input('actors')) {
                foreach ($request->input('actors') as $id) {
                    if ($movie->actors()->where('actors.id', $id)->exists()) {
                        continue;
                    }

                    $movie->actors()->attach($id);
                }
            }

            $this->uploadFile($request, $movie);

            return response()->json([
                'message' => 'Create movie successfully!',
                'movie' => $movie
            ]);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Create movie fail'
            ],500);
        }


    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Movie $movie
     * @return Response
     */
    public function show(Movie $movie, $id)
    {
        try {
            return response()->json([
                'movie' => Movie::findOrFail($id)->append(['video','trailer', 'user_rated', 'rate']),
                'comments' => Movie::findOrFail($id)->comments
            ]);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Not found movie'
            ],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Movie $movie
     * @return Response
     */
    public function show_admin(Movie $movie, $id)
    {
        try {
            $this->authorize('view', $movie);

            return response()->json([
                'movie' => Movie::withoutGlobalScope('Published')->findOrFail($id)->append(['video','trailer', 'rates'])
            ]);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Not found movie'
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Movie $movie
     * @return Response
     */
    public function edit(Movie $movie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param \App\Models\Movie $movie
     * @return Response
     */
    public function update(Request $request, Movie $movie)
    {
        $this->authorize('update', $movie);

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:movies,id'],
            'name' => ['nullable', 'max:50', 'unique:movies,name,'.$request->input('id')],
            'description' => ['nullable', 'max:255'],
            'company' => ['nullable', 'max:50'],
            'url_video' => ['nullable', 'url'],
            'limit_age' => ['nullable', 'numeric', 'min:1', 'max:99'],
            'country_id' => ['exists:countries,id', 'nullable'],
            'is_series' => ['nullable', 'boolean'],
            'movie_duration' => ['nullable', 'numeric', 'min:1'],
            'director_id' => ['nullable', 'exists:directors,id'],
            'publication_time' => ['date_format:Y-m-d', 'nullable'],
            'thumbnail' => ['nullable', 'mimes:jpeg,jpg,png,gif','max:10000'],
            'poster' => ['nullable', 'mimes:jpeg,jpg,png,gif','max:10000'],
            'poster_sub' => ['nullable', 'mimes:jpeg,jpg,png,gif','max:10000'],
            'video' => 'nullable|mimes:mp4,mov,ogg,qt',
            'trailer' => 'nullable|mimes:mp4,mov,ogg,qt',
            'categories' => 'nullable|array',
            'categories.*' => 'numeric|exists:categories,id',
            'actors' => 'nullable|array',
            'actors.*' => 'numeric|exists:actors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        try {
            $movie = Movie::withoutGlobalScope('Published')->findOrFail($request->input('id'));
            $movie->name = $request->input('name') ?: $movie->name;
            $movie->description = $request->input('description') ?: $movie->description;
            $movie->company = $request->input('company') ?: $movie->company;
            $movie->url_video = $request->input('url_video') ?: $movie->url_video;
            $movie->limit_age = $request->input('limit_age') ?: $movie->limit_age;
            $movie->country_id = $request->input('country_id') ?: $movie->country_id;
            $movie->is_series = $request->input('is_series') ?: $movie->is_series;
            $movie->movie_duration = $request->input('movie_duration') ?: $movie->movie_duration;
            $movie->director_id = $request->input('director_id') ?: $movie->director_id;
            $movie->publication_time = $request->input('publication_time') ?: $movie->publication_time;
            $movie->save();

            if ($request->input('categories')) {
                $movie->categories()->detach();
                foreach ($request->input('categories') as $id) {
                    if ($movie->categories()->where('categories.id', $id)->exists()) {
                        continue;
                    }

                    $movie->categories()->attach($id);
                }
            }

            if ($request->input('actors')) {
                $movie->actors()->detach();
                foreach ($request->input('actors') as $id) {
                    if ($movie->actors()->where('actors.id', $id)->exists()) {
                        continue;
                    }

                    $movie->actors()->attach($id);
                }
            }

            $this->uploadFile($request, $movie);

            return response()->json([
                'message' => 'Update successfully!',
                'movie' => $movie
            ]);

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Not found movie',
                'error' => $e
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Movie $movie
     * @return Response
     */
    public function set_status(Request $request, Movie $movie)
    {
        try {
            $this->authorize('update', $movie);

            $validator = Validator::make($request->all(), [
                'ids' => ['required', 'array'],
                'status' => ['required', function($attribute, $value, $fail) {
                    $movie_status = collect([MovieStatus::Published, MovieStatus::Archived]);
                    if (!$movie_status->contains($value)) {
                        return  $fail("The status field is invalid.");
                    }
                }]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Data Invalid',
                    'errors' => $validator->errors(),
                ], 500);
            }
            if (!$request->input('ids') ||
                !is_array($request->input('ids')) ||
                empty($request->input('ids')))
            {
                return response()->json([
                    'message' => 'Not found category',
                ], 500);
            }

            Movie::withoutGlobalScope('Published')->findOrFail($request->input('ids'))->each(function($movie) use ($request) {
                $movie->status = $request->input('status');
                $movie->save();
            });

            return response()->json([
                'message' => 'Update successfully!',
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'Not found movie',
                'error' => $exception
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Movie $movie
     * @return Response
     */
    public function destroy(Request $request, Movie $movie)
    {
        try {
            $this->authorize('delete', $movie);

            if (!$request->input('ids') ||
                !is_array($request->input('ids')) ||
                empty($request->input('ids')))
            {
                return response()->json([
                    'message' => 'Not found category',
                ], 500);
            }

            Movie::withoutGlobalScope('Published')->findOrFail($request->input('ids'))->each(fn($movie) => $movie->delete());
            return response()->json([
                'message' => 'Delete successfully!',
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'Not found movie',
                'error' => $exception
            ], 500);
        }
    }

    public function rate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:movies,id'],
            'rate' => ['required','numeric', 'max:5', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        try {
            $movie = Movie::findOrFail($request->get('id'));
            $this->authorize('rate', $movie);

            if (Rate::where('user_id', Auth::user()->id)->where('movie_id', $request->get('id'))->exists()) {
                return response()->json([
                    'message' => 'This user has rated this movie',
                ], 500);
            }
            DB::transaction(function () use ($request)
            {
                Activity::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $request->get('id'),
                    'object_type' => Movie::class,
                    'description' => 'User #' . Auth::user()->id . ' rated ' . $request->get('rate') . ' in movie #' . $request->get('id'),
                    'content' => $request->get('rate'),
                    'type' => ActivityType::Rate,
                ]);

                Rate::create([
                    'user_id' => Auth::user()->id,
                    'movie_id' => $request->get('id'),
                    'rate' => $request->get('rate'),
                ]);
            });

            return response()->json([
                'message' => 'Rate successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Rate failed',
            ], 500);
        }
    }

    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:movies,id'],
            'content' => ['required', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }
        try {
            $movie = Movie::findOrFail($request->get('id'));
            $this->authorize('comment', $movie);

            DB::transaction(function () use ($request)
            {
                Activity::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $request->get('id'),
                    'object_type' => Movie::class,
                    'description' => 'User #' . Auth::user()->id . ' commented as "' . $request->get('content') . '" in movie #' . $request->get('id'),
                    'content' => $request->get('content'),
                    'type' => ActivityType::Comment,
                ]);

                Comment::create([
                    'user_id' => Auth::user()->id,
                    'movie_id' => $request->get('id'),
                    'content' => $request->get('content'),
                ]);
            });

            return response()->json([
                'message' => 'Comment successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Comment failed',
            ], 500);
        }
    }

    public function reply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:comments,id'],
            'content' => ['required', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        try {
            DB::transaction(function () use ($request)
            {
                Activity::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $request->get('id'),
                    'object_type' => Comment::class,
                    'description' => 'User #' . Auth::user()->id . ' replied as "' . $request->get('content') . '" in comment #' . $request->get('id'),
                    'content' => $request->get('content'),
                    'type' => ActivityType::Reply,
                ]);

                Reply::create([
                    'user_id' => Auth::user()->id,
                    'comment_id' => $request->get('id'),
                    'content' => $request->get('content'),
                ]);
            });

            return response()->json([
                'message' => 'Reply successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Reply failed',
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param $movie
     * @return void
     */
    protected function uploadFile(Request $request, $movie): void
    {
        if ($request->hasFile('thumbnail')) {
            $movie->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
        }

        if ($request->hasFile('poster')) {
            $movie->addMediaFromRequest('poster')->toMediaCollection('poster');
        }

        if ($request->hasFile('poster_sub')) {
            $movie->addMediaFromRequest('poster_sub')->toMediaCollection('poster_sub');
        }

        if ($request->hasFile('video')) {
            $movie->addMediaFromRequest('video')->toMediaCollection('video');
        }

        if ($request->hasFile('trailer')) {
            $movie->addMediaFromRequest('trailer')->toMediaCollection('trailer');
        }
    }
}
