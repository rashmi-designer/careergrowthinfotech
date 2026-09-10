<?php
declare(strict_types=1);

function project_config_load_env(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $separatorPosition = strpos($line, '=');
        if ($separatorPosition === false) {
            continue;
        }

        $name = trim(substr($line, 0, $separatorPosition));
        $value = trim(substr($line, $separatorPosition + 1));

        if ($name === '') {
            continue;
        }

        if ((strlen($value) >= 2) && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            $value = stripcslashes(substr($value, 1, -1));
        }

        $_ENV[$name] = $value;
        if (function_exists('putenv')) {
            putenv($name . '=' . $value);
        }
    }
}

function project_config_get(string $key, $default = null)
{
    static $loaded = false;

    if (!$loaded) {
        project_config_load_env(__DIR__ . '/../.env');
        $loaded = true;
    }

    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    $value = trim((string) $value);

    return $value !== '' ? $value : $default;
}
