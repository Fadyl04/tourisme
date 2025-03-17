<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $request->validate([
            'user_name' => 'required|string',
            'user_email' => 'required|string|email|unique:users',
            'user_phone' => 'required|string',
            'user_password' => 'required|string|min:8|confirmed',
            'id_type_user' => 'required|integer|exists:type_users,id',
        ]);
        $user = User::create([
            'id_type_user' => $request->id_type_user,
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'user_phone' => $request->user_phone,
            'user_password' => Hash::make($request->user_password),
            'first_login' => true,
        ]);
        return response()->json(
            [
                'message'=>'Utilisateur enregistré', 
                'user'=>$user
            ], 200
        );

    }

    public function login(Request $request){
        $request->validate([
            'user_email' => 'required|email',
            'user_password' => 'required|string'
        ]);
        $user = User::where('user_email', $request->user_email)->first();
        if (!$user || !Hash::check($request->user_password, $user->user_password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json(
            [
                'message'=>'Utilisateur connecté',
                'token' => $token
            ],  200
        );
    }
    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }
}
