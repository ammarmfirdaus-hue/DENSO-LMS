<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/Core/DX_LMS_M_Request_Middleware.php';
require_once dirname(__DIR__, 2) . '/includes/Security.php';

securityTest('public route IDs are opaque, random per generation, and tamper resistant', static function (): void {
    $courseId = 'course_7';
    $first = publicCourseId($courseId);
    $second = publicCourseId($courseId);

    // Each issuance must produce a distinct randomized token via random IV
    assertSameValue(false, $first === $second, 'Tokens should be distinct across issuances');
    assertSameValue($courseId, resolveCourseId($first));
    assertSameValue($courseId, resolveCourseId($second));

    // Type mismatch returns null
    assertNullValue(resolvePublicRouteId('session', $first));

    // Tampered token must fail GCM authentication
    $parts = explode('.', $first);
    $parts[1] = $parts[1] . 'tampered';
    assertNullValue(resolvePublicRouteId('course', implode('.', $parts)));

    // Session and Thread helpers
    $sessId = 'session_12';
    $sessToken1 = publicSessionId($sessId);
    $sessToken2 = publicSessionId($sessId);
    assertSameValue(false, $sessToken1 === $sessToken2);
    assertSameValue($sessId, resolveSessionId($sessToken1));

    $threadId = '42';
    $threadToken1 = publicThreadId($threadId);
    $threadToken2 = publicThreadId($threadId);
    assertSameValue(false, $threadToken1 === $threadToken2);
    assertSameValue($threadId, resolveThreadId($threadToken1));
});

securityTest('middleware rejects path traversal and dangerous script payloads', static function (): void {
    $reflection = new ReflectionClass(DX_LMS_M_Request_Middleware::class);
    $hasPathTraversal = $reflection->getMethod('hasPathTraversal');
    $hasScriptInjection = $reflection->getMethod('hasScriptInjection');
    $hasSqlInjection = $reflection->getMethod('hasSqlInjection');

    // Path traversal
    assertSameValue(true, $hasPathTraversal->invoke(null, '../../etc/passwd'));
    assertSameValue(true, $hasPathTraversal->invoke(null, '..\\..\\windows\\system32'));
    assertSameValue(true, $hasPathTraversal->invoke(null, ['safe', '%2e%2e/secret']));
    assertSameValue(false, $hasPathTraversal->invoke(null, 'valid_slug_123'));

    // Script injection
    assertSameValue(true, $hasScriptInjection->invoke(null, '<script>alert(1)</script>'));
    assertSameValue(true, $hasScriptInjection->invoke(null, 'javascript:alert(1)'));
    assertSameValue(true, $hasScriptInjection->invoke(null, '<iframe src="evil"></iframe>'));
    assertSameValue(false, $hasScriptInjection->invoke(null, 'Normal text without tags'));

    // SQL injection
    assertSameValue(true, $hasSqlInjection->invoke(null, "1' union select 1,2,3--"));
    assertSameValue(true, $hasSqlInjection->invoke(null, "information_schema.tables"));
    assertSameValue(false, $hasSqlInjection->invoke(null, "normal-query-param"));
});
