<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ActorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Actor $actor)
    {
        $this->authorize('viewAny', $actor);

        $search = Str::of($request->get('search'))->trim();
        if ($search != "") {
            return response()->json([
                'actors' => Actor::where('name', 'like', "%$search%")->orderBy('created_at', 'desc')->paginate($request->get('limit') ?: 5),
            ],200);
        }

        if ($request->get('limit') != "") {
            return response()->json([
                'actors' => Actor::orderBy('created_at', 'desc')->paginate($request->get('limit')),
            ],200);
        }

        return response()->json([
            'actors' => Actor::orderBy('created_at', 'desc')->get()
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Actor $actor)
    {
        $this->authorize('create', $actor);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50'],
            'description' => ['nullable', 'max:255'],
            'country_id' => ['exists:countries,id', 'nullable'],
            'avatar' => $request->hasFile('avatar') ? ['mimes:jpeg,jpg,png,gif','max:10000'] :[],
            'date_of_birth' => ['date_format:Y-m-d','before:today','nullable'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        $actor = Actor::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'country_id' => $request->input('country_id'),
            'date_of_birth' => $request->input('date_of_birth')
        ]);

        if ($request->hasFile('avatar')){
            $actor->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        return response()->json([
            'message' => 'Create actor successfully!',
            'actor' => $actor
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Actor  $actor
     * @return \Illuminate\Http\Response
     */
    public function show(Actor $actor, $id)
    {
        try {
            return response()->json([
                'actor' => Actor::findOrFail($id)
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Not found actor'
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Actor  $actor
     * @return \Illuminate\Http\Response
     */
    public function edit(Actor $actor)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Actor  $actor
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Actor $actor)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:actors,id'],
            'name' => ['nullable', 'max:50'],
            'description' => ['nullable', 'max:255'],
            'country_id' => ['exists:countries,id', 'nullable'],
            'avatar' => $request->hasFile('avatar') ? ['mimes:jpeg,jpg,png,gif','max:10000'] :[],
            'date_of_birth' => ['date_format:Y-m-d','before:today','nullable'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        try {
            $actor = Actor::findOrFail($request->input('id'));
            $this->authorize('update', $actor);

            $actor->name = $request->input('name') ?: $actor->name;
            $actor->description = $request->input('description') ?: $actor->description;
            $actor->country_id = $request->input('country_id') ?: $actor->country_id;
            $actor->date_of_birth = $request->input('date_of_birth') ?: $actor->date_of_birth;
            $actor->save();

            if ($request->hasFile('avatar')){
                $actor->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }

            return response()->json([
                'message' => 'Update successfully!',
                'actor' => $actor
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Not found actor',
                'error' => $e
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Actor  $actor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'numeric|exists:actors,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Data Invalid',
                    'errors' => $validator->errors(),
                ], 500);
            }

            $delete_fail = collect();
            Actor::findOrFail($request->input('ids'))->each(fn($actor) =>
                Auth::user()->can('delete', $actor) ?
                $actor->delete() : $delete_fail->push($actor)
            );

            return response()->json([
                'message' => 'Delete successfully!',
                'delete_fail' => $delete_fail
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'Not found actor',
                'error' => $exception
            ], 500);
        }
    }
}
