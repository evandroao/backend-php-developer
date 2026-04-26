<?php

namespace App\Integration\Client;

use Illuminate\Support\Facades\Http;

/**
 * Classe base para chamadas a APIs externas.
 * Equivalente ao AbstractRequest<T>.java do projeto Spring Boot.
 *
 * NOTA: O método getShow() está retornando null propositalmente.
 * O candidato deve implementar a chamada HTTP real.
 */
class AbstractRequest
{
    /**
     * Realiza uma requisição GET para a URL informada.
     *
     * @param string $url URL da API externa
     * @return array|null Resposta da API como array associativo
     */
    public function getShow(string $url): ?array
    {
          $response = Http::get($url);

          if ($response->failed()) {
              return null;
          }

          return $response->json();
    }
}
