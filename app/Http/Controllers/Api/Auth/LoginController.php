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


    /**
     * @OA\Post(
     *  tags={"Login"},
     *  path="/api/login",
     *  summary="Realiza login no sistema",
     *  description="Retorna token de autenticação",
     *  @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="JSON",
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="email",
     *                  description="Email do usuário",
     *                  type="string",
     *                  example="email@email.com"
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  description="Senha do usuário",
     *                  type="string",
     *                  format="password",
     *                  example="senha2023"
     *              ),
     *          ),
     *      ),
     *  ),
     *  @OA\Response(response=200, description="Operação bem sucedida"),
     *  @OA\Response(response=500, description="Erro de servidor interno"),
     * )
     *
     */
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

    /**
     * @OA\Post(
     *  tags={"Logout"},
     *  path="/api/logout",
     *  summary="Realiza logout no sistema",
     *  description="Logout do sistema",
     *
     *  @OA\Response(response=200, description="Operação bem sucedida"),
     *  @OA\Response(response=500, description="Erro de servidor interno"),
     * @OA\SecurityScheme(
     *         type="http",
     *         securityScheme="bearerToken",
     *         scheme="bearer",
     *         bearerFormat="JWT",
     *     ),
     *     security={{"bearerToken": {}}},
     * )
     *
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
