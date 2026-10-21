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
        $this->view('DX_LMS_M_Auth_Login', ['csrf_token' => csrfToken()]);
    }

    public function processLogin(): void
    {
        startAppSession();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrf()) {
            http_response_code(403);
            exit('Permintaan tidak valid. Muat ulang halaman lalu coba kembali.');
        }
        $npk = trim((string) ($_POST['npk'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($npk === '' || strlen($npk) > 50 || $password === '' || strlen($password) > 200) {
            header('Location: /?route=/login&error=invalid', true, 303);
            exit;
        }
        $pdo = db();
        $ipHash = hash_hmac('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), DB_NAME . DB_PASSWORD);
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
        $statement = $pdo->prepare("SELECT student_id,npk,fullname,password_hash FROM student_accounts WHERE npk=:npk AND status='active' LIMIT 1");
        $statement->execute(['npk' => $npk]);
        $account = $statement->fetch();
        static $dummyHash = null;
        $dummyHash ??= password_hash(random_bytes(32), PASSWORD_DEFAULT);
        if (!password_verify($password, $account['password_hash'] ?? $dummyHash)) {
            $window = strtotime(($rateState['window_started_at'] ?? gmdate('Y-m-d H:i:s')) . ' UTC');
            $attempts = ($window < time() - 900) ? 1 : ((int) ($rateState['attempts'] ?? 0) + 1);
            $lockedUntil = $attempts >= 8 ? gmdate('Y-m-d H:i:s', time() + 900) : null;
            $update = $pdo->prepare('UPDATE login_rate_limits SET attempts=:attempts,window_started_at=IF(:reset_window=1,UTC_TIMESTAMP(),window_started_at),locked_until=:locked WHERE ip_hash=:ip');
            $update->execute(['attempts' => $attempts, 'reset_window' => $window < time() - 900 ? 1 : 0, 'locked' => $lockedUntil, 'ip' => $ipHash]);
            $pdo->commit();
            header('Location: /?route=/login&error=1', true, 303);
            exit;
        }
        $pdo->prepare('UPDATE login_rate_limits SET attempts=0,locked_until=NULL,window_started_at=UTC_TIMESTAMP() WHERE ip_hash=:ip')->execute(['ip' => $ipHash]);
        $pdo->commit();

        session_regenerate_id(true);
        $_SESSION['student_id'] = (int) $account['student_id'];
        $_SESSION['student_npk'] = $account['npk'];
        $_SESSION['student_name'] = $account['fullname'];
        $_SESSION['user'] = ['npk' => $account['npk'], 'name' => $account['fullname'], 'role' => 'student'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: /?route=/login', true, 303);
        exit;
    }
}
