<?php
declare(strict_types=1);

securityTest('router resolves a valid controller from its module directory', static function (): void {
    $router = new DX_LMS_M_Router();
    $method = new ReflectionMethod($router, 'findControllerFile');
    $path = $method->invoke($router, 'DX_LMS_M_Auth');
    assertTrueValue(is_string($path));
    assertTrueValue(str_ends_with(str_replace('\\', '/', $path), 'Controllers/Auth/DX_LMS_M_Auth.php'));
    assertTrueValue(is_file($path));
});

securityTest('router blocks traversal syntax and arbitrary file lookup attempts', static function (): void {
    $router = new DX_LMS_M_Router();
    $method = new ReflectionMethod($router, 'findControllerFile');
    foreach (['../Config', '..\\..\\includes\\Config', 'DX_LMS_M_Auth/../../index', '/etc/passwd', ''] as $attack) {
        assertNullValue($method->invoke($router, $attack), 'rejected controller: ' . $attack);
    }
});
