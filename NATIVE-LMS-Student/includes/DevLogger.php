<?php
declare(strict_types=1);

if (!function_exists('dlsPhpLog')) {
    function dlsPhpLog(string $level, string $action, string $resource, string $message): void
    {
        $ts = date('Y-m-d H:i:s');
        $lvl = strtoupper($level);
        $act = strtoupper($action);
        $res = strtoupper($resource);
        $clean = preg_replace('/(password|token|secret|apikey|api_key|authorization)\s*[:=]\s*\S+/i', '$1=[REDACTED]', $message);
        $clean = trim(str_replace(["\r", "\n"], ' ', (string) $clean));
        $line = "[{$ts}] [PHP] [{$lvl}] [{$act}] [{$res}] {$clean}" . PHP_EOL;
        $stderr = @fopen('php://stderr', 'wb');
        if ($stderr) {
            @fwrite($stderr, $line);
            @fclose($stderr);
        } else {
            @error_log(rtrim($line));
        }
    }

    function dlsInstallDevErrorHandlers(): void
    {
        static $installed = false;
        if ($installed) {
            return;
        }
        $installed = true;
        $reqStart = microtime(true);

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            $warnTypes = [E_WARNING, E_NOTICE, E_USER_WARNING, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED];
            $level = in_array($severity, $warnTypes, true) ? 'WARNING' : 'ERROR';
            $shortFile = basename($file);
            dlsPhpLog($level, 'RUNTIME', 'PHP_ERROR', "{$message} ({$shortFile}:{$line})");
            return false;
        });

        set_exception_handler(static function (Throwable $e): void {
            $shortFile = basename($e->getFile());
            dlsPhpLog('ERROR', 'EXCEPTION', 'UNCAUGHT', get_class($e) . ': ' . $e->getMessage() . " ({$shortFile}:{$e->getLine()})");
            if (!headers_sent()) {
                http_response_code(500);
                echo 'Terjadi kesalahan internal pada aplikasi Student.';
            }
        });

        register_shutdown_function(static function () use ($reqStart): void {
            $lastError = error_get_last();
            $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
            if ($lastError && in_array($lastError['type'], $fatalTypes, true)) {
                $shortFile = basename((string) $lastError['file']);
                dlsPhpLog('CRITICAL', 'FATAL', 'PHP_SHUTDOWN', "{$lastError['message']} ({$shortFile}:{$lastError['line']})");
            }

            $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
            $path = (string) (parse_url($uri, PHP_URL_PATH) ?? '/');
            if ($path === '/__dev_reload_stamp' || preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|map)$/i', $path)) {
                return;
            }
            $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
            $status = http_response_code();
            if ($status === false) {
                $status = 200;
            }
            $route = (string) ($_GET['route'] ?? $path);
            $durationMs = round((microtime(true) - $reqStart) * 1000, 1);

            $action = 'READ';
            if (strpos($route, '/auth/process') !== false || strpos($route, '/login') !== false && $method === 'POST') {
                $action = 'LOGIN';
            } elseif (strpos($route, '/auth/logout') !== false) {
                $action = 'LOGOUT';
            } elseif ($method === 'POST') {
                $action = 'CREATE';
            } elseif ($method === 'PUT' || $method === 'PATCH') {
                $action = 'UPDATE';
            } elseif ($method === 'DELETE') {
                $action = 'DELETE';
            }

            $level = $status >= 500 ? 'ERROR' : ($status >= 400 ? 'WARNING' : 'INFO');
            dlsPhpLog($level, $action, 'HTTP', "{$method} {$route} -> {$status} ({$durationMs}ms)");
        });
    }
}

