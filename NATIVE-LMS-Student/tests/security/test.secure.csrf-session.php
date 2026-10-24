<?php
declare(strict_types=1);

securityTest('CSRF validation requires an exact session token match', static function (): void {
    startAppSession();
    $_SESSION['csrf_token'] = str_repeat('a', 64);

    $_SERVER['HTTP_X_CSRF_TOKEN'] = str_repeat('b', 64);
    assertSameValue(false, verifyCsrf());

    $_SERVER['HTTP_X_CSRF_TOKEN'] = ['array', 'payload'];
    assertSameValue(false, verifyCsrf());

    $_SERVER['HTTP_X_CSRF_TOKEN'] = str_repeat('a', 64);
    assertSameValue(true, verifyCsrf());
    unset($_SERVER['HTTP_X_CSRF_TOKEN']);
});

securityTest('session configuration uses strict ids and HttpOnly SameSite cookies', static function (): void {
    startAppSession();
    assertSameValue('1', ini_get('session.use_strict_mode'));
    assertSameValue('1', ini_get('session.use_only_cookies'));
    $cookie = session_get_cookie_params();
    assertSameValue(true, $cookie['httponly']);
    assertSameValue('Lax', $cookie['samesite']);
});
