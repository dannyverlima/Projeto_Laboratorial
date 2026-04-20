<?php

declare(strict_types=1);

/**
 * Carrega variaveis do arquivo .env para getenv e $_ENV.
 */
function loadEnvFile(string $envFilePath): void
{
    if (!is_file($envFilePath)) {
        return;
    }

    $lines = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmedLine = trim($line);

        if ($trimmedLine === '' || str_starts_with($trimmedLine, '#')) {
            continue;
        }

        $parts = explode('=', $trimmedLine, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        if ($key === '') {
            continue;
        }

        $normalizedValue = trim($value, "\"'");

        $_ENV[$key] = $normalizedValue;
        putenv($key . '=' . $normalizedValue);
    }
}

loadEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '.env');
