<?php
declare(strict_types=1);

final class DX_LMS_M_Request_Middleware
{
    private const WINDOW_SECONDS = 60;
    private const MAX_REQUESTS = 180;
    private const IP_WINDOW_SECONDS = 60;
    private const IP_MAX_REQUESTS = 300;

    public static function handle(): void
    {
        startAppSession();
        self::setSecurityHeaders();
        self::validateRequestShape();
        self::enforceIpRateLimit();
        self::enforceSessionRateLimit();
    }

    private static function setSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(self), payment=()');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('X-XSS-Protection: 1; mode=block');
        header('Cache-Control: no-store, private');
    }

    private static function validateRequestShape(): void
    {
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($contentLength > 8 * 1024 * 1024) {
            http_response_code(413);
            header('Retry-After: 60');
            exit('Ukuran request terlalu besar.');
        }
        $origin = trim((string) ($_SERVER['HTTP_ORIGIN'] ?? ''));
        if ($origin !== '') {
            $allowed = array_filter([
                rtrim((string) APP_URL, '/'),
                rtrim((string) SSO_FRONTEND_URL, '/'),
            ]);
            if (!in_array($origin, $allowed, true)) {
                http_response_code(403);
                exit('Origin tidak diizinkan.');
            }
        }
        if (self::containsControlByte($_GET) || self::containsControlByte($_POST)) {
            http_response_code(400);
            exit('Input request tidak valid.');
        }
        if (self::hasPathTraversal($_GET) || self::hasPathTraversal($_POST)) {
            http_response_code(400);
            exit('Request mencurigakan diblokir.');
        }
        if (self::hasScriptInjection($_GET)) {
            http_response_code(400);
            exit('Karakter berbahaya terdeteksi.');
        }
        if (self::hasSqlInjection($_GET)) {
            http_response_code(400);
            exit('Pola query tidak diizinkan.');
        }
    }

    private static function hasPathTraversal(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) if (self::hasPathTraversal($item)) return true;
            return false;
        }
        return is_string($value) && (
            str_contains($value, '../') || str_contains($value, '..\\') ||
            str_contains($value, '%2e%2e') || str_contains($value, '%2f') || str_contains($value, '%5c')
        );
    }

    private static function hasScriptInjection(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) if (self::hasScriptInjection($item)) return true;
            return false;
        }
        return is_string($value) && preg_match('/<\s*script|javascript:|data:text\/html|<\s*iframe|<\s*object/i', $value) === 1;
    }

    private static function hasSqlInjection(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) if (self::hasSqlInjection($item)) return true;
            return false;
        }
        return is_string($value) && preg_match('/(?:\bunion\s+select\b|\binformation_schema\b|\bwaitfor\s+delay\b|\bbenchmark\(|\bpg_sleep\(|--\s*$|\/\*.*?\*\/)/i', $value) === 1;
    }

    private static function containsControlByte(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) if (self::containsControlByte($item)) return true;
            return false;
        }
        return is_string($value) && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $value) === 1;
    }

    private static function enforceIpRateLimit(): void
    {
        static $buckets = [];
        $now = time();
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $bucket = $buckets[$ip] ?? null;
        if (!is_array($bucket) || $now - (int) ($bucket['started_at'] ?? 0) >= self::IP_WINDOW_SECONDS) {
            $bucket = ['started_at' => $now, 'count' => 0];
        }
        $bucket['count']++;
        $buckets[$ip] = $bucket;
        if ($bucket['count'] <= self::IP_MAX_REQUESTS) return;
        $retryAfter = max(1, self::IP_WINDOW_SECONDS - ($now - (int) $bucket['started_at']));
        http_response_code(429);
        header('Retry-After: ' . $retryAfter);
        exit('Terlalu banyak permintaan. Silakan coba lagi nanti.');
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
