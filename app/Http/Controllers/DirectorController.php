<?php

namespace App\Http\Controllers;

use App\Models\Director;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class DirectorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Director $director)
    {
        $this->authorize('viewAny', $director);

        $search = Str::of($request->get('search'))->trim();
        if ($search != "") {
            return response()->json([
                'directors' => Director::where('name', 'like', "%$search%")->paginate($request->get('limit') ?: 5),
            ],200);
        }

        if ($request->get('limit') != "") {
            return response()->json([
                'directors' => Director::paginate($request->get('limit')),
            ],200);
        }

        return response()->json([
            'directors' => Director::select('id', 'name')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Director $director)
    {
        $this->authorize('create', $director);

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

        $director = Director::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'country_id' => $request->input('country_id'),
            'date_of_birth' => $request->input('date_of_birth')
        ]);

        if ($request->hasFile('avatar')){
            $director->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        return response()->json([
            'message' => 'Create director successfully!',
            'director' => $director
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        try {
            return response()->json([
                'director' => Director::findOrFail($id)
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Not found director'
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Http\Response
     */
    public function edit(Director $director)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:directors,id'],
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
            $director = Director::findOrFail($request->input('id'));
            $this->authorize('update', $director);

            $director->name = $request->input('name') ?: $director->name;
            $director->description = $request->input('description') ?: $director->description;
            $director->country_id = $request->input('country_id') ?: $director->country_id;
            $director->date_of_birth = $request->input('date_of_birth') ?: $director->date_of_birth;
            $director->save();

            if ($request->hasFile('avatar')){
                $director->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }

            return response()->json([
                'message' => 'Update successfully!',
                'director' => $director
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Not found director',
                'error' => $e
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Director  $director
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Director $director)
    {
        try {
            $this->authorize('delete', $director);

            if (!$request->input('ids') ||
                !is_array($request->input('ids')) ||
                empty($request->input('ids')))
            {
                return response()->json([
                    'message' => 'Not found director',
                ], 500);
            }

            Director::findOrFail($request->input('ids'))->each(fn($director) => $director->delete());
            return response()->json([
                'message' => 'Delete successfully!',
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'Not found director',
                'error' => $exception
            ], 500);
        }
    }
}
