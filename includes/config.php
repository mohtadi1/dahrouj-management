<?php
/**
 * Société Dahrouj Import Textile - Configuration File
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dahrouj_textile');

// Application Configuration
define('APP_NAME', 'Société Dahrouj Import Textile');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/dahrouj-management');
define('APP_CURRENCY', 'TND');
define('APP_CURRENCY_SYMBOL', 'DT');
define('APP_TAX_RATE', 19);

// Determine base path dynamically
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$basePath = '';
if (strpos($scriptDir, '/modules/') !== false) {
    $basePath = '../../';
} elseif (strpos($scriptDir, '/modules') !== false) {
    $basePath = '../';
}
define('BASE_PATH', $basePath);

// Session Configuration
session_start();

// Timezone
date_default_timezone_set('Africa/Tunis');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }
    return $db;
}

// Helper Functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function formatMoney($amount) {
    return number_format($amount, 3, '.', ' ') . ' ' . APP_CURRENCY_SYMBOL;
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

function generateCode($prefix, $id) {
    return $prefix . '-' . date('Y') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);
}

function generateNumber($prefix) {
    return $prefix . date('Ymd') . rand(1000, 9999);
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['user_role'] === 'admin';
}

function isManager() {
    return isLoggedIn() && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'manager');
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Veuillez vous connecter pour accéder à cette page.';
        redirect('login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['error'] = 'Accès refusé. Droits administrateur requis.';
        redirect('index.php');
    }
}

function requireManager() {
    requireLogin();
    if (!isManager()) {
        $_SESSION['error'] = 'Accès refusé. Droits insuffisants.';
        redirect('index.php');
    }
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function logActivity($action, $entity_type = null, $entity_id = null, $details = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'] ?? null,
        $action,
        $entity_type,
        $entity_id,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

function getSetting($key, $default = null) {
    $settings = [
        'company_name' => 'Société Dahrouj Import Textile',
        'company_address' => 'Tunisie',
        'company_phone' => '',
        'company_email' => 'contact@dahrouj.tn',
        'company_tax_number' => '',
    ];
    return $settings[$key] ?? $default;
}

// Pagination Helper
function getPagination($total, $page = 1, $perPage = 20) {
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    
    return [
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => $totalPages,
        'offset' => $offset,
        'hasPrevious' => $page > 1,
        'hasNext' => $page < $totalPages
    ];
}

// CSRF Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
