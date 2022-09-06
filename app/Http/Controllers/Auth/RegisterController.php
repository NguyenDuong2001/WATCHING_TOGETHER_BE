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
                'email' => ['required', 'regex:/([a-zA-Z0-9]+)?([a-zA-Z0-9]+)\@([a-zA-Z0-9]+)([\.])([a-zA-Z0-9\.]+)/u', 'unique:App\Models\User,email'],
                'phone_number' => ['required', 'digits:10', 'unique:App\Models\User,phone_number'],
                'fullName' => ['required'],
                'password' => ['required', 'max:255', Password::min(8)],
                'address' => ['required','max:255'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 500,
                    'message' => 'Data Invalid',
                    'errors' => $validator->errors(),
                ]);
            }

            $user = new User;
            $user->name = $request->input('fullName');
            $user->phone_number = $request->input('phone_number');
            $user->email = $request->input('email');
            $user->address = $request->input('address');
            $user->password = Hash::make($request->input('password'));
            $user->remember_token = Str::random(10);
            $user->save();

            fake()->addProvider(new \Smknstd\FakerPicsumImages\FakerPicsumImagesProvider(fake()));
            $imageUrl = fake()->imageUrl(200,200);
            $user->addMediaFromUrl($imageUrl)->toMediaCollection('avatar');

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
