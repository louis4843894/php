<?php
// 資料庫連線設定
$db_host = 'localhost';
$db_name = 'student_portfolio';
$db_user = 'root';
$db_pass = '';

// 建立PDO連線
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    /* 資料庫查詢安全性的最可靠方法，因為它保證使用者輸入的資料永遠不會被視為可執行的 SQL */
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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
