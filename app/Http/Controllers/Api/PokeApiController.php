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

        //dd($response);

        // Verifique se a solicitação foi bem-sucedida
        if ($response->getStatusCode() == 200) {
            // Obtenha os dados da resposta
            $data = json_decode($response->getBody(), true);

            $pokemons = [];

            foreach ($data['results'] as $pokemon) {
                $pokemonName = $pokemon['name'];
                $pokemonUrl = $pokemon['url'];
                $pokemonDetails = $this->getPokemonDetails($pokemonName);
                $pokemons[] = [
                    'id' => $pokemonDetails['id'],
                    'name' => $pokemonName,
                    'url' => $pokemonUrl,
                    'image' => $pokemonDetails['sprites']['other']['dream_world']['front_default'],
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

    public function show($id)
    {
        // Use o ID recebido para buscar o Pokémon no seu backend
        $pokemonDetails = $this->getPokemonDetailsById($id);

        if ($pokemonDetails) {
            return response()->json($pokemonDetails);
        } else {
            return response()->json(['error' => 'Pokémon não encontrado'], 404);
        }
    }

    public function getPokemonDetailsById($id)
    {
        // Instancie o cliente Guzzle
        $client = new Client();

        // Faça uma solicitação à API da PokeAPI para obter detalhes do Pokémon por ID
        $response = $client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . $id);

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
