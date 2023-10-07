<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class UserController extends Controller
{


    public function authenticate(Request $request)
    {

        $user = User::where('email', $request->email)->first();



        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $accessToken = $user->createToken('authToken')->plainTextToken;

        return response()->json(['token' => $accessToken], 200);
    }

    public function index()
    {
        $users = User::whereNull('deleted_at')->get();

        if ($users) {
            return response()->json(['users' => $users], 200);
        } else {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }
    }


    public function store(Request $request)
    {

        $requestData = json_decode(file_get_contents('php://input'), true);
        $validator = Validator::make($requestData, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $requestData['name'],
            'email' => $requestData['email'],
            'password' => Hash::make($requestData['password']),

        ]);



        return response()->json(['user' => $user], 201);
    }


    public function show($id)
    {

        $user = User::find($id);
        if ($user) {
            return response()->json(['user' => $user], 200);
        } else {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }
    }



    public function update(Request $request, $id)
    {
        try {
            $userModel = new User();

            // Chame o método getUser a partir da instância
            $user = $userModel->getUser($id);


            if (!$user) {
                return response()->json(['error' => 'Usuário não encontrado.'], 404);
            }


            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required',
            ]);

            if ($validator->fails()) {

                return response()->json(['error' => $validator->errors()], 401);
            }



            $form_data = array(
                'name' => $request->name,
                'email' => $request->email,
            );

            $user->update($form_data);

            return response()->json(['user' => $user], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }
    }


    public function destroy($id)
    {
        $userModel = new User();
        $user = $userModel->getUser($id);

       

        if (!$user) {
            return response()->json(['error' => 'Usuário não encontrado.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Usuário deletado com sucesso.'], 200);
    }
}
