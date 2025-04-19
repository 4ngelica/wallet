<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{

    public function store(Request $request, User $user){

        try{

            $request->validate(User::rules(), User::feedback());

            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = $request->password;
            $user->document = $request->document;
            $user->type = $request->type;

            $user->save();
            $user->wallet()->create([]);
        
            return response()->json($user->with('wallet')->get(), 201);

        }catch(ValidationException $e){
            return response()->json(["error" => $e->errors()], $e->status);
        }
        catch(\Throwable $e){
            return response()->json("Server error", 500);
        }
    }

}