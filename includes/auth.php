<?php
/**
 * Authentication Handler
 */

class Auth {

    /**
     * Start secure session
     */
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }

    /**
     * Check if user is logged in
     */
    public static function check() {
        self::startSession();
        return isset($_SESSION['user_id']) && isset($_SESSION['user_logged_in']);
    }

    /**
     * Get current user data
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }

        try {
            $stmt = db()->prepare("SELECT id, username, email, full_name, avatar_url, role FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Auth error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Login user
     */
    public static function login($username, $password) {
        try {
            $stmt = db()->prepare("SELECT id, username, email, password_hash, full_name, role FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                self::startSession();

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['login_time'] = time();

                // Update last login
                $updateStmt = db()->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);

                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Logout user
     */
    public static function logout() {
        self::startSession();

        // Unset all session variables
        $_SESSION = [];

        // Destroy session cookie
        if (isset($_COOKIE[SESSION_NAME])) {
            setcookie(SESSION_NAME, '', time() - 3600, '/');
        }

        // Destroy session
        session_destroy();
    }

    /**
     * Require authentication (redirect if not logged in)
     */
    public static function requireLogin($redirectUrl = '/admin/login.php') {
        if (!self::check()) {
            header('Location: ' . $redirectUrl);
            exit;
        }

        // Check session timeout
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_LIFETIME)) {
            self::logout();
            header('Location: ' . $redirectUrl . '?timeout=1');
            exit;
        }

        // Refresh login time
        $_SESSION['login_time'] = time();
    }

    /**
     * Check if user is already logged in (for login page)
     */
    public static function requireGuest($redirectUrl = '/admin/') {
        if (self::check()) {
            header('Location: ' . $redirectUrl);
            exit;
        }
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        self::startSession();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        self::startSession();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
    }
}
