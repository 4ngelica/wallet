<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;


class AuthController extends Controller
{

    public function login(Request $request)
    {
        try {
            
            $request->validate([
                'email' => 'required',
                'password' => 'required'
            ]);

            $credentials = $request->only('email', 'password');

            if(!auth()->attempt($credentials)){
                return response()->json(["error" => 'Credenciais inválidas'], 401);
            }

            $user = auth()->user();
            
            $user->tokens->each(function (PersonalAccessToken $token) {
                $token->delete();
            });

            $token = $user->createToken('auth_token');

            return response()->json([
                'token' => $token->plainTextToken
            ]);

        }catch(ValidationException $e) {
            return response()->json(["error" => $e->errors()], 422);
        }
        catch(\Throwable $e) {
            return response()->json("Server error", 500);
        }
    }


    public function logout()
    {
        try {

            auth()->user()->tokens()->delete();
            return response()->json([], 204);
        
        } catch (\Throwable $th) {
            return response()->json("Server error", 500);
        }

    }


}