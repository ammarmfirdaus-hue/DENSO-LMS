<?php
declare(strict_types=1);

securityTest('HTML helper escapes script payloads and attribute quotes', static function (): void {
    $payload = '<svg/onload=alert(1)>" onfocus="alert(2)';
    $escaped = e($payload);
    assertSameValue('&lt;svg/onload=alert(1)&gt;&quot; onfocus=&quot;alert(2)', $escaped);
    assertSameValue(false, str_contains($escaped, '<svg'));
});
