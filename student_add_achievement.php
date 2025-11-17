<?php
$pageTitle = '新增學習成果 - 學生學習成果認證系統';
session_start();
require_once 'config.php';

// 檢查是否為學生
if (!isLoggedIn() || isAdmin()) {
    header('Location: login.php');
    exit;
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    if (empty($title)) {
        $_SESSION['error'] = "請輸入成果名稱！";
    } else {
        $sql = "INSERT INTO achievements (user_id, category, title, description, status) 
                VALUES (:user_id, :category, :title, :description, 'pending')";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':category' => $category,
            ':title' => $title,
            ':description' => $description
        ])) {
            $_SESSION['success'] = "學習成果新增成功，等待審核！";
            header('Location: student_achievements.php');
            exit;
        } else {
            $_SESSION['error'] = "新增失敗，請稍後再試！";
        }
    }
}

include 'header.php';
?>

<div class="card">
    <h2>新增學習成果</h2>
    <p style="color: #b0b0b0; margin-bottom: 20px;">新增的成果將會進入「待審核」狀態，審核通過後才會顯示在人才搜尋中。</p>
    
    <form method="POST">
        <div class="form-group">
            <label>類別 *</label>
            <select name="category" required>
                <option value="">請選擇類別...</option>
                <option value="subject">擅長科目</option>
                <option value="language">程式語言</option>
                <option value="competition">參與競賽</option>
                <option value="certificate">取得證照</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>成果名稱 *</label>
            <input type="text" name="title" placeholder="例如：PHP、MySQL、全國技能競賽、TQC 證照..." required>
        </div>
        
        <div class="form-group">
            <label>成果描述（選填）</label>
            <textarea name="description" placeholder="詳細描述您的學習成果、技能程度、相關經驗等..."></textarea>
        </div>
        
        <button type="submit" class="btn">提交</button>
        <a href="student_achievements.php" class="btn btn-secondary">返回</a>
    </form>
</div>

<?php include 'footer.php'; ?>
