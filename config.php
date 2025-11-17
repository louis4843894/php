<?php
// 資料庫連線設定
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_portfolio');
define('DB_USER', 'root');
define('DB_PASS', '');

// 建立PDO連線
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}

// 開啟Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 檢查是否登入
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 檢查是否為管理員
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// 取得當前使用者ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// 重導向函數
function redirect($url) {
    header("Location: $url");
    exit();
}
?>
