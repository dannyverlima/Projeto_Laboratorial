<?php

declare(strict_types=1);

function envValue(string $key, ?string $default = null): ?string
{
    $fromEnv = $_ENV[$key] ?? getenv($key);

    if ($fromEnv === false || $fromEnv === null || $fromEnv === '') {
        return $default;
    }

    return (string) $fromEnv;
}

return [
    'data_provider' => envValue('DATA_PROVIDER', 'mock'), // mock | supabase
    'supabase' => [
        'url' => envValue('SUPABASE_URL', ''),
        'anon_key' => envValue('SUPABASE_ANON_KEY', ''),
        'schema' => envValue('SUPABASE_SCHEMA', 'public'),
        'tables' => [
            'consumo_diario' => envValue('SUPABASE_TABLE_CONSUMO', 'consumo_diario'),
            'eventos_escola' => envValue('SUPABASE_TABLE_EVENTOS', 'eventos_escola'),
            'qualidade_ar' => envValue('SUPABASE_TABLE_QUALIDADE_AR', 'qualidade_ar'),
        ],
    ],
];
