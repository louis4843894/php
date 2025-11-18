<?php
require_once 'config.php';

// 清空所有 session 內的資料
$_SESSION = array();
//刪掉 session 檔案
session_destroy();

// 導向首頁
redirect('index.php');
?>
