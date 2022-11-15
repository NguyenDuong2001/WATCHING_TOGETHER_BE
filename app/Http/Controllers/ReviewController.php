<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Enums\ReviewStatus;
use App\Enums\RoleType;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\Rate;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $reviews = Review::where('status', ReviewStatus::Published)->paginate($request->query('limit') ?: 5);
        return response()->json([
            'reviews' => $reviews->append('video'),
            'last_page' => $reviews->lastPage(),
            'current_page' => $reviews->currentPage(),
            'total_reviews' => $reviews->total()
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['nullable', function($attribute, $value, $fail) {
                $review_status = collect([
                    ReviewStatus::Pending,
                    ReviewStatus::Published,
                    ReviewStatus::Archived,
                    ReviewStatus::Canceled,
                ]);;
                if (!$review_status->contains($value)) {
                    return  $fail("The status field is invalid.");
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        $reviews = Review::where('author_id', Auth::user()->id)
            ->when($request->query('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->orderBy('updated_at', $request->query('sortBy') === 'last' ? 'asc' : 'desc')
            ->paginate($request->query('limit') ?: 5);

        return response()->json([
            'reviews' => $reviews->items(),
            'last_page' => $reviews->lastPage(),
            'current_page' => $reviews->currentPage()
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index_admin(Request $request, Review $review)
    {
        $this->authorize('viewAny', $review);

        $validator = Validator::make($request->all(), [
            'status' => ['nullable', function($attribute, $value, $fail) {
                $review_status = collect([
                    ReviewStatus::Pending,
                    ReviewStatus::Published,
                    ReviewStatus::Archived,
                    ReviewStatus::Canceled,
                ]);;
                if (!$review_status->contains($value)) {
                    return  $fail("The status field is invalid.");
                }
            }],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        $reviews = Review::when($request->query('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->orderBy('updated_at', $request->query('sortBy') === 'last' ? 'asc' : 'desc')
            ->paginate($request->query('limit') ?: 5);

        return response()->json([
            'reviews' => $reviews->append('checker'),
            'last_page' => $reviews->lastPage(),
            'current_page' => $reviews->currentPage()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Review $review)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50', 'unique:reviews'],
            'description' => ['nullable', 'max:255'],
            'movie_id' => ['required', 'exists:movies,id'],
            'thumbnail' => ['required', 'mimes:jpeg,jpg,png,gif','max:10000'],
            'video' => 'required|mimes:mp4,mov,ogg,qt',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        try {
            DB::transaction(function () use ($request) {
                $review = Review::create([
                    'name' => $request->get('name'),
                    'description' => $request->get('description'),
                    'movie_id' => $request->get('movie_id'),
                    'author_id' => Auth::user()->id,
                ]);

                if ($request->hasFile('thumbnail')) {
                    $review->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
                }

                if ($request->hasFile('video')) {
                    $review->addMediaFromRequest('video')->toMediaCollection('video');
                }

                Activity::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $request->get('movie_id'),
                    'object_type' => Movie::class,
                    'description' => 'User #' . Auth::user()->id . ' created review #' . $review->id . ' in movie #' . $request->get('movie_id'),
                    'type' => ActivityType::Review,
                ]);

            });

            return response()->json([
                'message' => 'Create review successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Create review failed',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Review $review, $id)
    {
        try {
            $review = Review::where('status', ReviewStatus::Published)->where('id', $id)->firstOrFail();

            return response()->json([
                'review' => $review->append('video')
            ]);
        }catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Mot found review'
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function manage(Review $review, $id)
    {
        try {
            $review = Review::findOrFail($id);
            $this->authorize('manage', $review);

            return response()->json([
                'review' => Auth::user()->role != RoleType::Customer ? $review->append('video', 'checker') : $review->append('video')
            ]);
        }catch (\Exception $e)
        {
            return response()->json([
                'message' => 'Mot found review'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Review $review)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:reviews,id',
            'name' => ['nullable', 'max:50', 'unique:reviews,name,'.$request->input('id')],
            'description' => ['nullable', 'max:255'],
            'movie_id' => ['nullable', 'exists:movies,id'],
            'thumbnail' => ['nullable', 'mimes:jpeg,jpg,png,gif','max:10000'],
            'video' => 'nullable|mimes:mp4,mov,ogg,qt',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        try {
            $review = Review::findOrFail($request->get('id'));
            $this->authorize('update', $review);

            DB::transaction(function () use ($request, $review) {
                $review->name = $request->get('name') ?: $review->name;
                $review->description = $request->get('description') ?: $review->description;
                $review->movie_id = $request->get('movie_id') ?: $review->movie_id;
                $review->status = ReviewStatus::Pending;
                $review->save();

                if ($request->hasFile('thumbnail')) {
                    $review->addMediaFromRequest('thumbnail')->toMediaCollection('thumbnail');
                }

                if ($request->hasFile('video')) {
                    $review->addMediaFromRequest('video')->toMediaCollection('video');
                }

                Activity::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $request->get('movie_id') ?: $review->movie_id,
                    'object_type' => Movie::class,
                    'description' => 'User #' . Auth::user()->id . ' updated review #' . $review->id . ' in movie #' . $request->get('movie_id') ?: $review->movie_id,
                    'type' => ActivityType::Review,
                ]);
            });

            return response()->json([
                'message' => 'Update review successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Update review failed',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        //
    }

    public function set_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'numeric|exists:movies,id',
            'status' => ['required', function ($attribute, $value, $fail) {
                $review_status = collect([
                    ReviewStatus::Pending,
                    ReviewStatus::Published,
                    ReviewStatus::Archived,
                    ReviewStatus::Canceled,
                ]);
                if (!$review_status->contains($value)) {
                    return $fail("The status field is invalid.");
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

        try {
            $review = Review::findOrFail($request->get('id'));
            $this->authorize('set_status', [$review, $request->get('status')]);
            $review->status = $request->get('status');
            $review->checker_id = Auth::user()->id;
            $review->save();

            return response()->json([
                'message' => 'Set status review successfully!',
            ]);
        }catch (\Exception $exception) {
            return response()->json([
                'message' => 'Set status review fail',
            ], 500);
        }
    }

    public function rate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:reviews,id'],
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
            $review = Review::where('status', ReviewStatus::Published)->where('id', $request->get('id'))->firstOrFail();
            $this->authorize('rate', $review);

            DB::transaction(function () use ($request, $review)
            {
                Activity::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $request->get('id'),
                    'object_type' => Review::class,
                    'description' => 'User #' . Auth::user()->id . ' rated ' . $request->get('rate') . ' in review #' . $review->id,
                    'content' => $request->get('rate'),
                    'type' => ActivityType::Rate,
                ]);

                Rate::updateOrCreate([
                    'user_id' => Auth::user()->id,
                    'object_id' => $review->id,
                    'object_type' => Review::class,
                ],[
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:reviews,id'],
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
            $review = Review::where('status', ReviewStatus::Published)->where('id', $request->get('id'))->firstOrFail();
            $this->authorize('comment', $review);

            DB::transaction(function () use ($request, $review)
            {
                Activity::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $review->id,
                    'object_type' => Review::class,
                    'description' => 'User #' . Auth::user()->id . ' commented as "' . $request->get('content') . '" in review #' . $review->id,
                    'content' => $request->get('content'),
                    'type' => ActivityType::Comment,
                ]);

                Comment::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $review->id,
                    'object_type' => Review::class,
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
}
