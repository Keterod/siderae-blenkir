<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MlRiskService
{
    public function predict(array $payload): array
    {
        $base = rtrim((string) config('services.ml.url'), '/');

        if ($base === '') {
            Log::warning('ML_SERVICE_URL vacía; no se puede llamar a /predict.');

            throw new \RuntimeException('El servicio de riesgo no está configurado.');
        }

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post("{$base}/predict", $payload);
        } catch (ConnectionException $e) {
            Log::warning('Fallo de conexión con ML predict', [
                'message' => $e->getMessage(),
            ]);

            throw new \RuntimeException('No se pudo conectar con el servicio de riesgo.');
        }

        if (! $response->successful()) {
            Log::warning('Respuesta HTTP no exitosa de ML predict', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('El servicio de riesgo no respondió correctamente.');
        }

        $json = $response->json();

        if (! is_array($json) || ! array_key_exists('indice_riesgo', $json)) {
            Log::warning('Respuesta ML predict sin indice_riesgo', ['body' => $response->body()]);

            throw new \RuntimeException('Respuesta del servicio de riesgo inválida.');
        }

        return $json;
    }
}
