<?php
/**
 * PMPD Security Library
 * Provides password hashing, CSRF protection, and session management
 */

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

/**
 * Initialize secure session with timeout checking
 */
function pmpd_session_init() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check for session timeout
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive >= SESSION_TIMEOUT) {
            // Session has expired
            pmpd_session_destroy();
            return false;
        }
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Destroy session securely
 */
function pmpd_session_destroy() {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Hash a password using bcrypt
 */
function pmpd_hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify a password against a hash
 * Also supports legacy plaintext comparison for migration
 */
function pmpd_verify_password($password, $hash) {
    // Check if it's a bcrypt hash (starts with $2)
    if (substr($hash, 0, 2) === '$2') {
        return password_verify($password, $hash);
    }
    // Legacy plaintext comparison (for migration)
    return $password === $hash;
}

/**
 * Check if password needs rehashing (for migration)
 */
function pmpd_needs_rehash($hash) {
    // If not a bcrypt hash, needs rehashing
    if (substr($hash, 0, 2) !== '$2') {
        return true;
    }
    return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Generate CSRF token
 */
function pmpd_csrf_generate() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF hidden input field
 */
function pmpd_csrf_field() {
    $token = pmpd_csrf_generate();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Validate CSRF token
 */
function pmpd_csrf_validate($token = null) {
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }

    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenerate CSRF token (call after successful form submission)
 */
function pmpd_csrf_regenerate() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Get Plex token from environment or config
 */
function pmpd_get_plex_token() {
    // First check environment variable
    $envToken = getenv('PLEX_TOKEN');
    if ($envToken !== false && !empty($envToken)) {
        return $envToken;
    }

    // Fall back to config file
    global $plexToken;
    return $plexToken ?? '';
}

/**
 * Check if user is authenticated with timeout
 */
function pmpd_is_authenticated() {
    if (!pmpd_session_init()) {
        return false;
    }

    return isset($_SESSION['username']) && !empty($_SESSION['username']);
}

/**
 * Get remaining session time in seconds
 */
function pmpd_session_remaining() {
    if (!isset($_SESSION['last_activity'])) {
        return 0;
    }

    $remaining = SESSION_TIMEOUT - (time() - $_SESSION['last_activity']);
    return max(0, $remaining);
}
?>
