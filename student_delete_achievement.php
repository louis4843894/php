<?php
session_start();
require_once 'config.php';


if ($achievement && $achievement['status'] === 'pending') {
    // 刪除成果
    $sql = "DELETE FROM achievements WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    
    $_SESSION['success'] = "學習成果已刪除！";
} else {
    $_SESSION['error'] = "無法刪除此成果！";
}

header('Location: student_achievements.php');
exit;
