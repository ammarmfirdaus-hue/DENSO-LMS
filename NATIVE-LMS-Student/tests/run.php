<?php
declare(strict_types=1);

putenv('APP_ENV=testing');
putenv('SSO_JWT_SECRET=native-tests-only-secret-value-32-characters-minimum');
putenv('SSO_JWT_ISSUER=dls-test-suite');
putenv('SSO_JWT_AUDIENCE=dls-test-suite');
putenv('SESSION_NAME=DLS_TEST_SESSION');

$projectRoot = dirname(__DIR__);
chdir($projectRoot);
require __DIR__ . '/support.php';
require_once $projectRoot . '/includes/Security.php';
require_once $projectRoot . '/includes/Pagination.php';
require_once $projectRoot . '/includes/DX_LMS_M_Functions.php';
require_once $projectRoot . '/Core/DX_LMS_M_Router.php';

foreach (glob(__DIR__ . '/test.secure.*.php') ?: [] as $suite) {
    require $suite;
}

$results = [];
foreach ($GLOBALS['nativeSecurityTests'] as [$name, $test]) {
    try {
        $test();
        $results[] = [true, $name, ''];
    } catch (Throwable $error) {
        $results[] = [false, $name, $error->getMessage()];
    }
}

$failed = 0;
foreach ($results as [$passed, $name, $message]) {
    if ($passed) {
        echo "[PASS] $name\n";
        continue;
    }
    $failed++;
    fwrite(STDERR, "[FAIL] $name\n       $message\n");
}
echo sprintf("%d security checks, %d failed.\n", count($results), $failed);
exit($failed === 0 ? 0 : 1);
