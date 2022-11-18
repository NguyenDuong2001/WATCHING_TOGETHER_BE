<?php

namespace App\Http\Controllers\Auth;

use App\Models\Room;
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
                "name" => $request->input('fullName'),
                "email" => $request->input('email'),
                "remember_token" => Str::random(10),
                "address" => $request->input('address'),
                "phone_number" => $request->input('phone_number'),
                "password" => Hash::make($request->input('password')),
            ]);

//           $user->addMediaFromUrl("https://robohash.org/".rand(1,1000))->toMediaCollection('avatar');

            Room::create(['user_id' => $user->id]);

            return response()->json([
            'token' => 'Bearer '.$user->createToken('authToken')->plainTextToken,
            ], 200);
        }catch (\Exception $error) {
            return response()->json([
                'message' => 'Error in Sign up',
                'errors' => $error,
            ], 500);
        }
    }
}
