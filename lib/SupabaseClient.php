<?php

declare(strict_types=1);

final class SupabaseClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $schema;

    public function __construct(string $baseUrl, string $apiKey, string $schema = 'public')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->schema = $schema;
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== '' && $this->apiKey !== '';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function select(string $table, string $select = '*', array $query = []): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Supabase nao configurado.');
        }

        $url = $this->baseUrl . '/rest/v1/' . rawurlencode($table);
        $queryString = http_build_query(array_merge(['select' => $select], $query));

        $ch = curl_init($url . '?' . $queryString);
        if ($ch === false) {
            throw new RuntimeException('Nao foi possivel iniciar requisicao CURL.');
        }

        $headers = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json',
            'Content-Type: application/json',
            'Accept-Profile: ' . $this->schema,
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 12,
        ]);

        $responseBody = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false || $error !== '') {
            throw new RuntimeException('Erro ao consultar Supabase: ' . $error);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new RuntimeException('Supabase respondeu com status ' . $statusCode . '.');
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Resposta invalida do Supabase.');
        }

        return $decoded;
    }
}
