<?php

namespace App\Http\Controllers;

use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, User $user)
    {
        $this->authorize('viewAny', $user);

        $search = Str::of($request->get('search'))->trim();
        if ($search != "") {
            return response()->json([
                'users' => User::where('name', 'like', "%$search%")->paginate($request->get('limit') ?: 5),
            ],200);
        }

        return response()->json([
            'users' => User::paginate($request->get('limit') ?: 5)
        ], 200);
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
    public function store(Request $request, User $user)
    {
        try{
            if (!$request->user()->can('create', $user)){
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:50'],
                'address' => ['required','max:255'],
                'role_id' => ['exists:roles,id', 'required'],
                'country_id' => ['exists:countries,id', 'nullable'],
                'password' => ['required', 'max:255', Password::min(8)],
                'avatar' => $request->hasFile('avatar') ? ['mimes:jpeg,jpg,png,gif','max:10000'] :[],
                'date_of_birth' => ['date_format:Y-m-d','before:today','nullable'],
                'email' => ['required', 'regex:/([a-zA-Z0-9]+)?([a-zA-Z0-9]+)\@([a-zA-Z0-9]+)([\.])([a-zA-Z0-9\.]+)/u', 'unique:App\Models\User,email'],
                'phone_number' => ['required', 'digits:10', 'unique:App\Models\User,phone_number'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Data Invalid',
                    'errors' => $validator->errors(),
                ], 500);
            }

            $user = User::create([
                "name" => $request->input('name'),
                "email" => $request->input('email'),
                "remember_token" => Str::random(10),
                "address" => $request->input('address'),
                "role_id" => $request->input("role_id"),
                "country_id" => $request->input("country_id"),
                "phone_number" => $request->input('phone_number'),
                "date_of_birth" => $request->input("date_of_birth"),
                "password" => Hash::make($request->input('password')),
            ]);

            if ($request->hasFile('avatar')){
                $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }

            return response()->json([
                'message' => 'Create user successfully!',
                'user' => $user
            ], 200);
        }catch (\Exception $error){
            \Log::error($error);

            return response()->json([
                'message' => 'Create user errors',
                'error' => $error
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user, $id)
    {
        try {
            return response()->json([
                'user' => User::findOrFail($id)
            ], 200);
        }catch (\Exception $e){
            return response()->json([
                'message' => 'Not found user'
            ],500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request)
    {
        //_method=PUT
        try{
            $validator = Validator::make($request->all(), [
                'name' => ['max:50','nullable'],
                'address' => ['nullable','max:255'],
                'role_id' => ['exists:roles,id', 'nullable'],
                'country_id' => ['exists:countries,id', 'nullable'],
                'password' => ['nullable', 'max:255', Password::min(8)],
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
            $user = User::findOrFail(Auth::user()->id);

            if (!$request->user()->can('update', $user)){
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            if (Auth::user()->role->name === RoleType::SuperAdmin && $request->input('role_id') && $request->input('ids')){
                $update_fail = collect([]);
                foreach ($request->input('ids') as $id){
                    if (Auth::user()->id == $id) {
                        $update_fail->push(Auth::user());
                        continue;
                    }

                    $user = User::findOrFail($id);
                    $user->role_id = $request->input('role_id');
                    $user->save();
                }

                return response()->json([
                    'message' => 'Update successfully!',
                    'update_fail' => $update_fail
                ]);
            }

            if ($request->input('password') && !Hash::check($request->input('old_password'), $user->password)){
                return response()->json([
                    'message' => 'Old password invalid',
                ], 500);
            }

            $user->name = $request->input('name') ? $request->input('name') : $user->name;
            $user->address = $request->input('address') ? $request->input('address') : $user->address;
            $user->country_id = $request->input('country_id') ? $request->input('country_id') : $user->country_id;
            $user->password = $request->input('password') ? Hash::make($request->input('password')) : $user->password;
            $user->date_of_birth = $request->input('date_of_birth') ? $request->input('date_of_birth') : $user->date_of_birth;
            $user->save();

            if ($request->hasFile('avatar')){
                $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
            }

            return response()->json([
                'message' => 'Update successfully!',
                'user' => $user
            ], 200);

        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Not found user',
                'error' => $e
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'numeric|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Data Invalid',
                    'errors' => $validator->errors(),
                ], 500);
            }

            $delete_fail = collect();
            User::findOrFail($request->input('ids'))->each(fn ($user) =>
                Auth::user()->can('delete', $user) ? $user->delete() : $delete_fail->push($user)
            );

            return response()->json([
                'message' => 'Delete successfully!',
                'delete_fail' => $delete_fail
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'Not found user',
                'error' => $exception
            ], 500);
        }
    }
}
