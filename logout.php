<?php
require_once 'config.php';

// 清除所有Session資料
$_SESSION = array();
session_destroy();

// 導向首頁
redirect('index.php');
?>
