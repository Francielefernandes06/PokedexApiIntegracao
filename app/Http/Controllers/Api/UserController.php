<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
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



    /**
 * @OA\Get(
 *     path="/api/user-by-name",
 *     summary="Buscar usuários por nome e obter informações sobre Pokémon favoritos",
 *     tags={"Usuários"},
 *     @OA\Parameter(
 *         name="name",
 *         in="query", 
 *         required=true,
 *         description="Nome para busca",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuários encontrados com sucesso",
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Erro de validação dos dados",
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Nenhum usuário encontrado com esse nome",
 *     ),
 *     @OA\SecurityScheme(
 *         type="http",
 *         securityScheme="bearerToken",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *     ),
 *     security={{"bearerToken": {}}},
 * )
 */

    public function searchByName(Request $request)
    {

        //dd('test');
        $name = $request->input('name');

        if (empty($name)) {
            return response()->json(['error' => 'Por favor, forneça um nome para buscar.'], 400);
        }

        $users = User::with('favorites')->where('name', 'like', '%' . $name . '%')->get();

        if ($users->isEmpty()) {
            return response()->json(['error' => 'Nenhum usuário encontrado com esse nome.'], 404);
        }
        $users->each(function ($user) {
            $user->favorites->each(function ($favorite) {
                $pokemonData = $this->getPokemonInfo($favorite->pokemon_id);
                $favorite->pokemon_info = $pokemonData;
            });
        });

        return response()->json($users);
    }


    public function getPokemonInfo($pokemonId)
    {
        // Instancie o cliente Guzzle
        $client = new Client();

        // Faça uma solicitação à API da PokeAPI para obter detalhes do Pokémon por ID
        $response = $client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . $pokemonId);

        // Verifique se a solicitação foi bem-sucedida
        if ($response->getStatusCode() == 200) {
            // Obtenha os dados da resposta
            $pokemonData = json_decode($response->getBody(), true);
            //dd($pokemonData['stats']);

            $totalStats = 0;
            foreach ($pokemonData['stats'] as $stat) {
                $totalStats += $stat['base_stat'];
            }

            // Construa os detalhes do Pokémon que você deseja retornar
            $pokemonDetails = [
                'id' => $pokemonData['id'],
                'name' => $pokemonData['name'],
                'type' => $pokemonData['types'][0]['type']['name'], // Suponho que você deseja o primeiro tipo
                'hp' => $pokemonData['stats'][0]['base_stat'], // Suponho que 'hp' está no índice 0
                'attack' => $pokemonData['stats'][1]['base_stat'], // Suponho que 'attack' está no índice 1
                'defense' => $pokemonData['stats'][2]['base_stat'], // Suponho que 'defense' está no índice 2
                'special_attack' => $pokemonData['stats'][3]['base_stat'], // Suponho que 'special_attack' está no índice 3
                'special_defense' => $pokemonData['stats'][4]['base_stat'], // Suponho que 'special_defense' está no índice 4
                'speed' => $pokemonData['stats'][5]['base_stat'], // Suponho que 'speed' está no índice 5
                'total_stats' => $totalStats,
                'ability' => $pokemonData['abilities'][0]['ability']['name'], // Suponho que você deseja a primeira habilidade
                'photo' => $pokemonData['sprites']['other']['home']['front_default'],

            ];

            return response()->json($pokemonDetails);
        } else {
            // Trate erros, se necessário
            return response()->json(['error' => 'Falha ao buscar detalhes do Pokémon'], 500);
        }
    }
}
