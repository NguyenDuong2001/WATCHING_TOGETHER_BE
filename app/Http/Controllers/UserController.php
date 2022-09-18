<?php

namespace App\Http\Controllers;

use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
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
        if (!$request->user()->can('viewAny', $user)){
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'users' => User::all()
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
                'avatar' => ['mimes:jpeg,jpg,png,gif', 'nullable','max:10000'],
                'date_of_birth' => ['date_format:Y-m-d','before:today','nullable'],
                'email' => ['required', 'regex:/([a-zA-Z0-9]+)?([a-zA-Z0-9]+)\@([a-zA-Z0-9]+)([\.])([a-zA-Z0-9\.]+)/u', 'unique:App\Models\User,email'],
                'phone_number' => ['required', 'digits:10', 'unique:App\Models\User,phone_number'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Data Invalid',
                    'errors' => $validator->errors(),
                ]);
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
    public function show(User $user)
    {
        //
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
    public function update(Request $request, $id = null)
    {
        //_method=PUT
        try{
            $validator = Validator::make($request->all(), [
                'name' => ['max:50','nullable'],
                'address' => ['nullable','max:255'],
                'role_id' => ['exists:roles,id', 'nullable'],
                'country_id' => ['exists:countries,id', 'nullable'],
                'password' => ['nullable', 'max:255', Password::min(8)],
                'avatar' => ['mimes:jpeg,jpg,png,gif', 'nullable','max:10000'],
                'date_of_birth' => ['date_format:Y-m-d','before:today','nullable'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Data Invalid',
                    'errors' => $validator->errors(),
                ]);
            }
            $user = User::findOrFail($id ? $id : Auth::user()->id);
            if (!$request->user()->can('update',$user)){
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            if (Auth::user()->role->name === RoleType::SuperAdmin && $request->input('role_id')){
                $user->role_id = $request->input('role_id');
                $user->save();

                return response()->json([
                    'message' => 'Update successfully!',
                    'user' => $user
                ], 200);
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
    public function destroy(Request $request, User $user, $id)
    {
        try {
            if (!$request->user()->can('delete', $user)){
                return response()->json([
                    'message' => 'Unauthorized',
                ], 403);
            }

            User::findOrFail($id)->delete();

            return response()->json([
                'message' => 'Delete successfully!',
                'id' => $id
            ], 200);
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'Not found user',
                'error' => $exception
            ], 500);
        }
    }
}
