<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.api')->except('login', 'logout');
    }


    public function login()
    {
        $requestData = json_decode(file_get_contents('php://input'), true);

        $validator = Validator::make($requestData, [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $credentials = [
            'email' => $requestData['email'],
            'password' => $requestData['password']
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $accessToken = $user->createToken('authToken')->plainTextToken;
            return response()->json(['access_token' => $accessToken, 'user' => $user], 200);
        } else {
            return response()->json(['error' => 'Usuário não encontrado.'], 401);
        }
    }

   
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
