<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'fullName' => ['required'],
                'address' => ['required','max:255'],
                'password' => ['required', 'max:255', Password::min(8)],
                'phone_number' => ['required', 'digits:10', 'unique:App\Models\User,phone_number'],
                'email' => ['required', 'regex:/([a-zA-Z0-9]+)?([a-zA-Z0-9]+)\@([a-zA-Z0-9]+)([\.])([a-zA-Z0-9\.]+)/u', 'unique:App\Models\User,email'],
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
                "phone_number" => $request->input('phone_number'),
                "password" => Hash::make($request->input('password')),
            ]);

//           $user->addMediaFromUrl("https://robohash.org/".rand(1,1000))->toMediaCollection('avatar');

            return response()->json([
            'token' => 'Bearer '.$user->createToken('authToken')->plainTextToken,
            ], 200);
        }catch (\Exception $error) {
            Log::error($error);

            return response()->json([
                'message' => 'Error in Sign up',
            ], 500);
        }
    }
}
