<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
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
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => ['required'],
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
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
