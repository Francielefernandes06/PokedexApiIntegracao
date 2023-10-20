<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        // Recupere os Pokémons favoritos do usuário atual
        $favorites = Favorite::where('user_id', $user->id)->get();

        // Crie uma instância do cliente Guzzle
        $client = new Client();

        $detailedFavorites = [];

        // Para cada Pokémon favorito, faça uma chamada à PokeAPI para obter informações detalhadas
        foreach ($favorites as $favorite) {
            $pokemonId = $favorite->pokemon_id;
            $response = $client->get("https://pokeapi.co/api/v2/pokemon/{$pokemonId}/");

            if ($response->getStatusCode() === 200) {
                $pokemonData = json_decode($response->getBody());

                $totalStats = 0;
                foreach ($pokemonData->stats as $stat) {
                    $totalStats += $stat->base_stat;
                }
                // Extraia as informações desejadas do Pokémon
                $pokemonInfo = [
                    'id' => $pokemonData->id,
                    'name' => $pokemonData->name,
                    'type' => $pokemonData->types[0]->type->name,
                    'hp' => $pokemonData->stats[0]->base_stat,
                    'attack' => $pokemonData->stats[1]->base_stat,
                    'defense' => $pokemonData->stats[2]->base_stat,
                    'special_attack' => $pokemonData->stats[3]->base_stat,
                    'special_defense' => $pokemonData->stats[4]->base_stat,
                    'speed' => $pokemonData->stats[5]->base_stat,
                    'total_stats' => $totalStats,
                    'ability' => $pokemonData->abilities[0]->ability->name,
                    'photo' => $pokemonData->sprites->other->home->front_default,
                ];

                $detailedFavorites[] = $pokemonInfo;
            }
        }

        return response()->json($detailedFavorites);
    }

    /**
     * @OA\Post(
     *     path="/api/favorites",
     *     summary="Favoritar pokemon",
     *     tags={"Favorites"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Favoritar Pokemon",
     *         @OA\JsonContent(
     *   @OA\Property(property="pokemon_id", type="integer", example=10),

     *
     *                                 )
     *                             ),
     *
     *
     *     @OA\Response(response=201, description="pokemon favoritado com sucesso"),
     *     @OA\Response(response=400, description="Requisição inválida"),
     *     @OA\Response(response=500, description="Erro de servidor interno"),
     *     @OA\SecurityScheme(
     *         type="http",
     *         securityScheme="bearerToken",
     *         scheme="bearer",
     *         bearerFormat="JWT",
     *     ),
     *     security={{"bearerToken": {}}},
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $pokemonId = $request->input('pokemon_id');

        // Verifique se o usuário já tem o Pokémon em seus favoritos
        $existingFavorite = Favorite::where('user_id', $user->id)
            ->where('pokemon_id', $pokemonId)
            ->first();

        if ($existingFavorite) {
            return response()->json(['message' => 'Este Pokémon já está em seus favoritos.'], 422);
        }

        // Verifique se o usuário tem pontuação suficiente para adicionar o Pokémon
        $pokemonCost = 100; // Altere esta quantidade conforme necessário
        if ($user->pontuation < $pokemonCost) {
            return response()->json(['message' => 'Você não tem pontuação suficiente para adicionar este Pokémon aos favoritos.'], 422);
        }

        // Deduza a pontuação do usuário
        $user->pontuation -= $pokemonCost;
        $user->save();

        // Crie um novo favorito
        $favorite = new Favorite();
        $favorite->user_id = $user->id;
        $favorite->pokemon_id = $pokemonId;
        $favorite->date_added = now(); // Pode ser ajustado conforme necessário
        $favorite->save();

        return response()->json(['message' => 'Pokémon adicionado aos favoritos com sucesso.']);
    }


    /**
     * @OA\Delete(
     *     path="/api/favorites/{id}",
     *     summary="Excluir pokemon dos favoritos por ID",
     *     tags={"Favorites"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da pokemon a ser excluída dos favoritos",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="pokemon excluído dos favoritos com sucesso"),
     *     @OA\Response(response=404, description="Favorito não encontrado"),
     *     @OA\Response(response=500, description="Erro de servidor interno"),
     *    @OA\SecurityScheme(
     *         type="http",
     *         securityScheme="bearerToken",
     *         scheme="bearer",
     *         bearerFormat="JWT",
     *     ),
     *     security={{"bearerToken": {}}},
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('pokemon_id', $id)
            ->first();

        if (!$favorite) {
            return response()->json(['message' => 'Este Pokémon não está em seus favoritos.'], 422);
        }


        $user->pontuation += 50;
        $user->save();


        $favorite->delete();

        return response()->json(['message' => 'Pokémon removido dos favoritos com sucesso.']);
    }
}
