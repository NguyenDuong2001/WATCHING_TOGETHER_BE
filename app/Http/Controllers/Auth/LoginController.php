<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email_or_phone_number' => $request->login_from == 'email' ? ['required', 'regex:/([a-zA-Z0-9]+)?([a-zA-Z0-9]+)\@([a-zA-Z0-9]+)([\.])([a-zA-Z0-9\.]+)/u'] : ['required', 'digits:10'],
                'password' => ['required'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Email or password is invalid',
                    'errors' => $validator->errors(),
                ], 500);
            }


            if (!Auth::attempt([
                $request->login_from => $request->email_or_phone_number,
                'password'=> $request->password
                ])) {
                return response()->json([
                    'message' => 'Email or password is incorrect',
                    'errors' => [
                        'error' => ['Email or password is incorrect']
                    ]
                ], 500);
            }

            $user = User::where('email', $request->email_or_phone_number)->orWhere('phone_number', $request->email_or_phone_number)->first();

            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Error in Login');
            }

            $user->tokens()->delete();
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'token' => 'Bearer '.$tokenResult,
            ],200);
        } catch (\Exception $error) {
            return response()->json([
                'message' => 'Email or password is incorrect',
                'errors' => [
                    'error' => ['Email or password is incorrect']
                ]
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Log out successfully',
        ], 200);
    }
}
