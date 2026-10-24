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

$requestedSuite = $argv[1] ?? 'all';
$knownSuites = ['security', 'features'];
if ($requestedSuite !== 'all' && !in_array($requestedSuite, $knownSuites, true)) {
    fwrite(STDERR, "Usage: php tests/run.php [security|features|all]\n");
    exit(2);
}
$suites = $requestedSuite === 'all' ? $knownSuites : [$requestedSuite];
foreach ($suites as $suite) {
    foreach (glob(__DIR__ . '/' . $suite . '/test.*.php') ?: [] as $testFile) require $testFile;
}

$results = [];
foreach ($GLOBALS['nativeTests'] as [$suite, $name, $test]) {
    if (!in_array($suite, $suites, true)) continue;
    try {
        $test();
        $results[] = [true, $suite, $name, ''];
    } catch (Throwable $error) {
        $results[] = [false, $suite, $name, $error->getMessage()];
    }
}

$failed = 0;
foreach ($results as [$passed, $suite, $name, $message]) {
    if ($passed) {
        echo "[PASS][$suite] $name\n";
        continue;
    }
    $failed++;
    fwrite(STDERR, "[FAIL][$suite] $name\n       $message\n");
}
echo sprintf("%d checks (%s), %d failed.\n", count($results), implode(', ', $suites), $failed);
exit($failed === 0 ? 0 : 1);
