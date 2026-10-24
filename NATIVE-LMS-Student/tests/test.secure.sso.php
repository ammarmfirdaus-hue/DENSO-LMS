<?php
declare(strict_types=1);

securityTest('SSO accepts valid HS256 claims for the configured audience', static function (): void {
    $claims = verifySsoToken(makeSsoTokenForTest());
    assertTrueValue(is_array($claims));
    assertSameValue('employee-17', $claims['sub']);
    assertSameValue('teacher', $claims['role']);
});

securityTest('SSO rejects tampered signatures, malformed tokens, and algorithm substitution', static function (): void {
    $token = makeSsoTokenForTest();
    [$header, $body, $signature] = explode('.', $token);
    $tamperedBody = base64UrlEncodeForTest(json_encode(['sub' => 'attacker', 'role' => 'admin', 'iss' => SSO_JWT_ISSUER, 'aud' => SSO_JWT_AUDIENCE, 'exp' => time() + 600], JSON_THROW_ON_ERROR));
    assertNullValue(verifySsoToken($header . '.' . $tamperedBody . '.' . $signature));
    assertNullValue(verifySsoToken('not.a.valid.token'));
    assertNullValue(verifySsoToken('@@@.@@@.@@@'));

    $noneToken = base64UrlEncodeForTest('{"typ":"JWT","alg":"none"}') . '.' . $body . '.' . $signature;
    assertNullValue(verifySsoToken($noneToken));
});

securityTest('SSO rejects expired, future-issued, wrong-issuer, and wrong-audience tokens', static function (): void {
    assertNullValue(verifySsoToken(makeSsoTokenForTest(['exp' => time() - 1])));
    assertNullValue(verifySsoToken(makeSsoTokenForTest(['iat' => time() + 120])));
    assertNullValue(verifySsoToken(makeSsoTokenForTest(['iss' => 'attacker.local'])));
    assertNullValue(verifySsoToken(makeSsoTokenForTest(['aud' => 'different-application'])));
});

securityTest('student SSO token must carry a positive student id', static function (): void {
    assertNullValue(verifySsoToken(makeSsoTokenForTest(['role' => 'student'])));
    assertNullValue(verifySsoToken(makeSsoTokenForTest(['role' => 'student', 'student_id' => 0])));
    $claims = verifySsoToken(makeSsoTokenForTest(['role' => 'student', 'student_id' => 42]));
    assertTrueValue(is_array($claims));
    assertSameValue(42, $claims['student_id']);
});

securityTest('unknown roles and missing subject claims are rejected', static function (): void {
    assertNullValue(verifySsoToken(makeSsoTokenForTest(['role' => 'root'])));
    assertNullValue(verifySsoToken(makeSsoTokenForTest(['sub' => ''])));
});
