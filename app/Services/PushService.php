<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PushService
{
    /**
     * Envia uma notificação via FCM legacy endpoint para um conjunto de tokens.
     * Retorna o response body ou throws exception em falha.
     *
     * @param array $tokens
     * @param string $title
     * @param string $body
     * @param array $data
     */
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = [])
    {
        if (empty($tokens)) return null;

        $serverKey = env('FCM_SERVER_KEY');
        if (empty($serverKey)) {
            throw new \Exception('FCM_SERVER_KEY não configurado');
        }

        $payload = [
            'registration_ids' => array_values($tokens),
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);

        if (!$response->successful()) {
            throw new \Exception('Erro enviando push: ' . $response->body());
        }

        return $response->json();
    }
}
