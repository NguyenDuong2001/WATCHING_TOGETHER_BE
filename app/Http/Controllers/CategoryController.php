<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = Str::of($request->get('search'))->trim();
        if ($search != "") {
            return response()->json([
                'categories' => Category::where('name', 'like', "%$search%")->paginate($request->get('limit') ?: 5),
            ],200);
        }

        if ($request->get('limit') != "") {
            return response()->json([
                'categories' => Category::paginate($request->get('limit')),
            ],200);
        }

        return response()->json([
            'categories' => Category::all(),
        ],200);
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
    public function store(Request $request, Category $category)
    {
        $this->authorize('create', $category);

        $validator = Validator::make($request->all(), [
            'name' => ['unique:categories','required', 'max:50'],
            'description' => ['nullable', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        $category = Category::create([
            'name' => $request->input('name'),
            'description' => $request->input('description')
        ]);

        return response()->json([
            'message' => 'Create category successfully!',
            'category' => $category
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category, $id)
    {
        try {
            return response()->json([
                'category' => Category::findOrFail($id)
            ]);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Not found category'
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric', 'exists:categories,id'],
            'name' => ['unique:categories,name,'.$request->input('id'),'nullable', 'max:50'],
            'description' => ['nullable', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Data Invalid',
                'errors' => $validator->errors(),
            ], 500);
        }

        try {
            $category = Category::findOrFail($request->input('id'));
            $this->authorize('update', $category);

            $category->name = $request->input('name') ?: $category->name;
            $category->description = $request->input('description') ?: $category->description;
            $category->save();

            return response()->json([
                'message' => 'Update successfully!',
                'category' => $category
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Not found category',
                'error' => $e
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Category $category)
    {
        try {
            $this->authorize('delete', $category);

            if (!$request->input('ids') ||
                !is_array($request->input('ids')) ||
                empty($request->input('ids')))
            {
                return response()->json([
                    'message' => 'Not found category',
                ], 500);
            }

            Category::findOrFail($request->input('ids'))->each(fn($category) => $category->delete());
            return response()->json([
                'message' => 'Delete successfully!',
            ]);
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'Not found category',
                'error' => $exception
            ], 500);
        }
    }
}
