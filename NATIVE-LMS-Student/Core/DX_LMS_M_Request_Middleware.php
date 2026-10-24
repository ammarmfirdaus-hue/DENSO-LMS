<?php
declare(strict_types=1);

final class DX_LMS_M_Request_Middleware
{
    private const WINDOW_SECONDS = 60;
    private const MAX_REQUESTS = 180;

    public static function handle(): void
    {
        startAppSession();
        self::enforceSessionRateLimit();
    }

    private static function enforceSessionRateLimit(): void
    {
        $now = time();
        $window = $_SESSION['_request_limit'] ?? null;
        if (!is_array($window) || ($now - (int) ($window['started_at'] ?? 0)) >= self::WINDOW_SECONDS) {
            $window = ['started_at' => $now, 'count' => 0];
        }

        $window['count'] = (int) $window['count'] + 1;
        $_SESSION['_request_limit'] = $window;
        if ($window['count'] <= self::MAX_REQUESTS) return;

        $retryAfter = max(1, self::WINDOW_SECONDS - ($now - (int) $window['started_at']));
        http_response_code(429);
        header('Retry-After: ' . $retryAfter);
        header('Cache-Control: no-store');

        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            jsonResponse(['error' => ['code' => 'RATE_LIMITED', 'message' => 'Terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.']], 429);
        }

        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Terlalu banyak permintaan</title><body><main><h1>Terlalu banyak permintaan</h1><p>Silakan tunggu sebentar sebelum melanjutkan.</p></main></body></html>';
        exit;
    }
}
