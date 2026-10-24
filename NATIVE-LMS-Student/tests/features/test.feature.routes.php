<?php
declare(strict_types=1);

require_once APP_ROOT . '/Core/DX_LMS_M_Route_Catalog.php';

featureTest('Student page route catalog points to existing controller actions', static function (): void {
    $expectedPaths = [
        '/student', '/student/courses', '/student/courses/filters', '/student/riwayat',
        '/student/notifikasi', '/student/profil', '/student/profil/detail',
        '/student/profil/pengaturan', '/student/profil/bantuan', '/student/course/detail',
        '/student/forum/category', '/student/forum/thread', '/student/forum/new',
        '/student/riwayat/detail', '/student/learning', '/student/quiz',
    ];
    $routes = studentRouteCatalog();
    assertSameValue($expectedPaths, array_keys($routes), 'registered Student page paths');

    $router = new DX_LMS_M_Router();
    $resolver = new ReflectionMethod($router, 'findControllerFile');
    foreach ($routes as $path => $action) {
        [$controller, $method] = explode('@', $action, 2);
        $file = $resolver->invoke($router, $controller);
        assertTrueValue(is_string($file) && is_file($file), $path . ' controller file exists');
        $source = file_get_contents($file);
        assertTrueValue(is_string($source), $path . ' controller can be read');
        assertTrueValue(
            preg_match('/\bclass\s+' . preg_quote($controller, '/') . '\b[\s\S]*?\bfunction\s+' . preg_quote($method, '/') . '\s*\(/', $source) === 1,
            $path . ' action exists'
        );
    }
});

featureTest('Student logout controls submit POST with CSRF instead of navigating to GET', static function (): void {
    $component = file_get_contents(APP_ROOT . '/includes/components/DX_LMS_M_Student_Bottom_Nav.php');
    assertTrueValue(is_string($component), 'shared student navigation component can be read');
    assertTrueValue(
        preg_match('/<form\s+id="studentNavLogoutForm"[^>]*method="POST"[^>]*action="\?route=\/auth\/logout"/i', $component) === 1,
        'shared navigation exposes a POST logout form'
    );
    assertTrueValue(str_contains($component, 'name="_csrf" value="<?= e(csrfToken()) ?>"'), 'shared logout form includes a CSRF token');
    assertTrueValue(str_contains($component, 'window.submitStudentLogout()'), 'logout confirmation submits the protected form');
    assertTrueValue(!str_contains($component, "window.location.href = '?route=/auth/logout'"), 'logout controls do not fall back to an unsafe GET');

    $controller = file_get_contents(APP_ROOT . '/Controllers/Auth/DX_LMS_M_Auth.php');
    assertTrueValue(is_string($controller) && str_contains($controller, "\$_SERVER['REQUEST_METHOD'] === 'GET'"), 'direct GET opens a confirmation instead of rejecting the request');
    assertTrueValue(str_contains($controller, "'DX_LMS_M_Auth_Logout_Confirm'") && str_contains($controller, 'verifyCsrf()'), 'logout still requires CSRF validation before ending the session');
});
