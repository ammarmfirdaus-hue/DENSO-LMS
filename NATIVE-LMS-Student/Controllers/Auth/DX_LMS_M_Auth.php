<?php
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Security.php';

class DX_LMS_M_Auth extends DX_LMS_M_Controller
{
    public function login(): void
    {
        startAppSession();
        if (!empty($_SESSION['student_id'])) {
            header('Location: /?route=/student', true, 303);
            exit;
        }
        $claims = verifySsoToken((string) ($_COOKIE[SSO_COOKIE_NAME] ?? ''));
        if ($claims && ($claims['role'] ?? '') === 'student') {
            createStudentSession($claims);
            header('Location: /?route=/student', true, 303);
            exit;
        }
        if ($claims && in_array(($claims['role'] ?? ''), ['admin', 'teacher'], true)) {
            header('Location: ' . SSO_FRONTEND_URL, true, 303);
            exit;
        }
        if (!empty($_COOKIE[SSO_COOKIE_NAME]) && !$claims) clearSsoBrowserCookie();
        $this->view('DX_LMS_M_Auth_Login', ['csrf_token' => csrfToken()]);
    }

    public function processLogin(): void
    {
        startAppSession();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
            http_response_code(403);
            exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
        }
        $npkInput = $_POST['npk'] ?? '';
        $passwordInput = $_POST['password'] ?? '';
        if (!is_string($npkInput) || !is_string($passwordInput)) {
            header('Location: /?route=/login&error=invalid', true, 303);
            exit;
        }
        $npk = trim($npkInput);
        $password = $passwordInput;
        // Bcrypt rejects NUL bytes; reject them before either password_hash() or
        // password_verify() can receive attacker-controlled input.
        if ($npk === '' || strlen($npk) > 254 || $password === '' || strlen($password) > 200 || str_contains($password, "\0")) {
            header('Location: /?route=/login&error=invalid', true, 303);
            exit;
        }
        $pdo = db();
        $ipAndAccount = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\0" . strtolower($npk);
        $ipHash = hash_hmac('sha256', $ipAndAccount, DB_NAME . DB_PASSWORD);
        $pdo->beginTransaction();
        $pdo->prepare('INSERT IGNORE INTO login_rate_limits(ip_hash) VALUES(:ip)')->execute(['ip' => $ipHash]);
        $rate = $pdo->prepare('SELECT attempts,window_started_at,locked_until FROM login_rate_limits WHERE ip_hash=:ip FOR UPDATE');
        $rate->execute(['ip' => $ipHash]);
        $rateState = $rate->fetch();
        if ($rateState && $rateState['locked_until'] && strtotime($rateState['locked_until'] . ' UTC') > time()) {
            $pdo->commit();
            header('Location: /?route=/login&error=rate', true, 303);
            exit;
        }
        $pdo->commit();
        $requestBody = json_encode(['npk' => $npk, 'password' => $password], JSON_THROW_ON_ERROR);
        $context = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\nConnection: close\r\n",
            'content' => $requestBody,
            'timeout' => 10,
            'ignore_errors' => true
        ], 'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $responseBody = @file_get_contents(SSO_API_URL . '/auth/login', false, $context);
        $response = is_string($responseBody) ? json_decode($responseBody, true) : null;
        $ssoToken = '';
        foreach (($http_response_header ?? []) as $responseHeader) {
            if (preg_match('/^Set-Cookie:\s*' . preg_quote(SSO_COOKIE_NAME, '/') . '=([^;]*)/i', $responseHeader, $matches)) {
                $ssoToken = rawurldecode($matches[1]);
                break;
            }
        }
        $claims = $ssoToken !== '' ? verifySsoToken($ssoToken) : null;
        if (!$claims || ($claims['role'] ?? '') !== 'student' || (int) ($claims['student_id'] ?? 0) < 1) {
            $pdo->prepare("UPDATE login_rate_limits SET attempts=IF(window_started_at < UTC_TIMESTAMP() - INTERVAL 15 MINUTE,1,attempts+1),window_started_at=IF(window_started_at < UTC_TIMESTAMP() - INTERVAL 15 MINUTE,UTC_TIMESTAMP(),window_started_at),locked_until=IF(attempts+1>=8,UTC_TIMESTAMP() + INTERVAL 15 MINUTE,NULL) WHERE ip_hash=:ip")
                ->execute(['ip' => $ipHash]);
            if (($response['error']['code'] ?? '') === 'INVALID_CREDENTIALS') {
                header('Location: /?route=/login&error=1', true, 303);
                exit;
            }
            header('Location: /?route=/login&error=sso', true, 303);
            exit;
        }
        $pdo->prepare('UPDATE login_rate_limits SET attempts=0,locked_until=NULL,window_started_at=UTC_TIMESTAMP() WHERE ip_hash=:ip')->execute(['ip' => $ipHash]);
        setSsoBrowserCookie($ssoToken);
        createStudentSession($claims);
        header('Location: /?route=/student', true, 303);
        exit;
    }

    public function logout(): void
    {
        startAppSession();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
            http_response_code(403);
            exit('Permintaan tidak valid.');
        }
        $_SESSION = [];
        clearSsoBrowserCookie();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: /?route=/login', true, 303);
        exit;
    }
}
