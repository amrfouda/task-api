<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $r) {
        $data = $r->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6',
        ]);
        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>bcrypt($data['password']),
        ]);
        return ['token' => $user->createToken('api')->plainTextToken];
    }

    public function login(Request $r) {
        $data = $r->validate(['email'=>'required|email','password'=>'required']);
        if (!Auth::attempt($data)) {
            return response()->json(['message'=>'Invalid credentials'], 422);
        }
        return ['token' => $r->user()->createToken('api')->plainTextToken];
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        // If authenticated with a Personal Access Token (Bearer), revoke just that token
        $token = $user?->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
            return response()->noContent();
        }

        // Otherwise, it's likely a TransientToken (cookie-based session):
        // 1) revoke all tokens if you also issue PATs elsewhere (optional)
        // $user?->tokens()->delete();

        // If this ever runs under a session guard, only touch session IF it exists
        if ($request->hasSession()) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }
}
