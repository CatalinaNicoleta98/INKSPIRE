<?php
class Session {
    // Start the session safely
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Make $_SESSION accessible globally as $session
        $GLOBALS['session'] = &$_SESSION;

        // If user is logged in, make it globally available as $user
        if (isset($_SESSION['user'])) {
            $GLOBALS['user'] = $_SESSION['user'];
        }
    }

    // Automatically start when file loads
    public static function init() {
        self::start();
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;

        // Keep global variable updated
        $GLOBALS['session'] = &$_SESSION;
        if ($key === 'user') {
            $GLOBALS['user'] = $value;
        }
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public static function destroy() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Remove global variables
        unset($GLOBALS['session']);
        unset($GLOBALS['user']);
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user']);
    }
}

//  Automatically start and make globals available
Session::init();
