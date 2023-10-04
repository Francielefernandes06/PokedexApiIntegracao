<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PokeApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        //http://127.0.0.1:8000/api/pokemons?page=2&per_page=20

        // rota
        $page = $request->input('page', 1);

        // Número de Pokémon por página
        $perPage = 10; // Você pode ajustar esse valor conforme necessário

        // Calcule o offset com base no número da página
        $offset = ($page - 1) * $perPage;
        // Instancie o cliente Guzzle
        // Instancie o cliente Guzzle
        $client = new Client();

        // Faça a solicitação à API da PokeAPI para listar os Pokémon
        $response = $client->request('GET', 'https://pokeapi.co/api/v2/pokemon?limit=' . $perPage . '&offset=' . $offset);

        // Verifique se a solicitação foi bem-sucedida
        if ($response->getStatusCode() == 200) {
            // Obtenha os dados da resposta
            $data = json_decode($response->getBody(), true);

            $pokemons = [];

            foreach ($data['results'] as $pokemon) {
                $pokemonName = $pokemon['name'];
                $pokemonDetails = $this->getPokemonDetails($pokemonName);
                $pokemons[] = [
                    'name' => $pokemonName,
                    'image' => $pokemonDetails['sprites']['front_default'],
                ];
            }

            return response()->json($pokemons);
        } else {
            return response()->json(['error' => 'Erro ao listar os Pokémon'], 500);
        }
    }

    private function getPokemonDetails($name)
    {
        // Instancie o cliente Guzzle
        $client = new Client();

        // Faça a solicitação à API da PokeAPI para obter detalhes do Pokémon
        $response = $client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . $name);

        // Verifique se a solicitação foi bem-sucedida
        if ($response->getStatusCode() == 200) {
            // Obtenha os dados da resposta
            $data = json_decode($response->getBody(), true);

            return $data;
        } else {
            // Trate erros, se necessário
            return null;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
