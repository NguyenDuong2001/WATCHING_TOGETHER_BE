<?php

namespace App\Http\Controllers;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index_movie(Request $request, $id)
    {
        return response()->json([
            'comments' => Comment::where('object_id', $id)->where('object_type', Movie::class)
                ->orderBy('created_at', $request->query('sortBy') === 'last' ? 'asc' : 'desc')
                ->orderBy('id', $request->query('sortBy') === 'last' ? 'asc' : 'desc')
                ->limit($request->get('limit') ?: 5)->get(),
            'total' => Comment::where('object_id', $id)->where('object_type', Movie::class)->count()
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index_review(Request $request, $id)
    {
        return response()->json([
            'comments' => Comment::where('object_id', $id)->where('object_type', Review::class)
                ->orderBy('created_at', $request->query('sortBy') === 'last' ? 'asc' : 'desc')
                ->orderBy('id', $request->query('sortBy') === 'last' ? 'asc' : 'desc')
                ->limit($request->get('limit') ?: 5)->get(),
            'total' => Comment::where('object_id', $id)->where('object_type', Review::class)->count()
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Comment $comment)
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
            $comment = Comment::findOrFail($request->get('id'));
            $this->authorize('update', $comment);

            $comment->content = $request->get('content');
            $comment->save();

            Activity::create([
                'user_id' => Auth::user()->id,
                'object_id' => $comment->object_id,
                'object_type' => Movie::class,
                'description' => 'User #' . Auth::user()->id . ' updated comment #'.$comment->id.' as "' . $request->get('content') . '" in '. ($comment->object_type === Movie::class ? 'movie' : 'review').' #' . $comment->object_id,
                'content' => $request->get('content'),
                'type' => ActivityType::Comment,
            ]);

            return response()->json([
                'message' => 'Update comment successfully!',
                'comment' => $comment
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Not found comment',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:comments,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        try {
            DB::transaction(function () use($request) {
                $comment = Comment::findOrFail($request->get('id'));
                $this->authorize('delete', $comment);

                $comment->delete();

                Activity::create([
                    'user_id' => Auth::user()->id,
                    'object_id' => $comment->object_id,
                    'object_type' => Movie::class,
                    'description' => 'User #' . Auth::user()->id . ' deleted comment #'. $comment->id. ' in '. ($comment->object_type === Movie::class ? 'movie' : 'review').' #' . $comment->object_id,
                    'type' => ActivityType::Comment,
                ]);
            });

            return response()->json([
                'message' => 'Delete comment successfully!',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Not found comment',
            ], 500);
        }
    }
}
