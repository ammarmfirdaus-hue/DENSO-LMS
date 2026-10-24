<?php
declare(strict_types=1);

$GLOBALS['nativeSecurityTests'] = [];

function securityTest(string $name, callable $test): void
{
    $GLOBALS['nativeSecurityTests'][] = [$name, $test];
}

function assertSameValue(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(($message ? $message . ': ' : '') . 'expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

function assertTrueValue(bool $actual, string $message = ''): void
{
    assertSameValue(true, $actual, $message);
}

function assertNullValue(mixed $actual, string $message = ''): void
{
    assertSameValue(null, $actual, $message);
}

function base64UrlEncodeForTest(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function makeSsoTokenForTest(array $claims = [], array $header = []): string
{
    $now = time();
    $payload = array_merge([
        'sub' => 'employee-17',
        'role' => 'teacher',
        'iss' => SSO_JWT_ISSUER,
        'aud' => SSO_JWT_AUDIENCE,
        'iat' => $now,
        'exp' => $now + 600,
    ], $claims);
    $tokenHeader = array_merge(['typ' => 'JWT', 'alg' => 'HS256'], $header);
    $head = base64UrlEncodeForTest(json_encode($tokenHeader, JSON_THROW_ON_ERROR));
    $body = base64UrlEncodeForTest(json_encode($payload, JSON_THROW_ON_ERROR));
    $content = $head . '.' . $body;
    $signature = hash_hmac('sha256', $content, SSO_JWT_SECRET, true);
    return $content . '.' . base64UrlEncodeForTest($signature);
}
