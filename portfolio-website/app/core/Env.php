<?php

namespace App\Core;

/**
 * Environment Variable Loader
 *
 * Dependency-free .env reader. Resolution order for any key:
 *
 *   1. Real environment variables (what Docker Compose injects)
 *   2. A .env file next to the application root, or one directory above it
 *   3. The default passed to get()
 *
 * This keeps credentials out of version control while letting the same
 * codebase run under Docker, XAMPP, or cPanel without edits.
 */
class Env
{
    /** @var bool */
    private static $loaded = false;

    /** @var array<string,string> */
    private static $vars = [];

    /**
     * Parse the .env file once per request.
     *
     * @param string|null $path Explicit path, otherwise auto-discovered.
     * @return void
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $candidates = $path !== null
            ? [$path]
            : [
                // portfolio-website/.env  (XAMPP / bare-metal layout)
                dirname(__DIR__, 2) . '/.env',
                // repository-root/.env    (Docker Compose layout)
                dirname(__DIR__, 3) . '/.env',
            ];

        foreach ($candidates as $file) {
            if (!is_file($file) || !is_readable($file)) {
                continue;
            }

            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                continue;
            }

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                    continue;
                }

                list($key, $value) = explode('=', $line, 2);
                $key   = trim($key);
                $value = trim($value);

                // Strip one layer of matching quotes.
                $length = strlen($value);
                if ($length >= 2) {
                    $first = $value[0];
                    $last  = $value[$length - 1];
                    if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                        $value = substr($value, 1, -1);
                    }
                }

                if ($key !== '' && !array_key_exists($key, self::$vars)) {
                    self::$vars[$key] = $value;
                }
            }

            // First readable file wins.
            break;
        }
    }

    /**
     * Read an environment value.
     *
     * Strings "true"/"false" are cast to booleans; "null" becomes null.
     * An explicitly empty value is returned as an empty string, not the
     * default — an empty APP_BASE_URL is a meaningful setting.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        self::load();

        $value = getenv($key);

        if ($value === false) {
            if (array_key_exists($key, $_ENV)) {
                $value = $_ENV[$key];
            } elseif (array_key_exists($key, $_SERVER)) {
                $value = $_SERVER[$key];
            } elseif (array_key_exists($key, self::$vars)) {
                $value = self::$vars[$key];
            } else {
                return $default;
            }
        }

        if (!is_string($value)) {
            return $value;
        }

        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
        }

        return $value;
    }

    /**
     * Read a value as a boolean.
     *
     * @param string $key
     * @param bool   $default
     * @return bool
     */
    public static function bool($key, $default = false)
    {
        $value = self::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 'on', 'yes'], true);
    }

    /**
     * Read a value as an integer.
     *
     * @param string $key
     * @param int    $default
     * @return int
     */
    public static function int($key, $default = 0)
    {
        $value = self::get($key, null);

        return ($value === null || $value === '') ? $default : (int) $value;
    }

    /**
     * Require a value to be present. Fails loudly rather than silently
     * falling back to a wrong environment's credentials.
     *
     * @param string $key
     * @return mixed
     * @throws \RuntimeException
     */
    public static function required($key)
    {
        $value = self::get($key, null);

        if ($value === null || $value === '') {
            throw new \RuntimeException(
                "Missing required environment variable: {$key}. "
                . "Copy .env.example to .env and fill it in."
            );
        }

        return $value;
    }
}
