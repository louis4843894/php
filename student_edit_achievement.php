<?php
require_once 'config.php';

// 檢查是否為學生
if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$user_id = getCurrentUserId();
$achievement_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// 取得成果資料（必須是自己的且為待審核狀態）
$stmt = $pdo->prepare("
    SELECT * FROM achievements 
    WHERE id = ? AND user_id = ? AND status = 'pending'
");
$stmt->execute([$achievement_id, $user_id]);
$achievement = $stmt->fetch();

if (!$achievement) {
    redirect('student_achievements.php');
}

// 處理更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($category) || empty($title)) {
        $error = '類別和成果名稱不能為空';
    } else {
        $stmt = $pdo->prepare("
            UPDATE achievements 
            SET category = ?, title = ?, description = ? 
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        if ($stmt->execute([$category, $title, $description, $achievement_id, $user_id])) {
            $success = '成果更新成功';
            // 重新載入資料
            $stmt = $pdo->prepare("SELECT * FROM achievements WHERE id = ?");
            $stmt->execute([$achievement_id]);
            $achievement = $stmt->fetch();
        } else {
            $error = '更新失敗，請稍後再試';
        }
    }
}

$pageTitle = '編輯成果 - 學生學習成果認證系統';
include 'header.php';
?>

<h2>編輯學習成果</h2>

<div style="margin-bottom: 15px;">
    <!-- 更新返回連結路徑 -->
    <a href="student_achievements.php">← 返回成果列表</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="">
        <div class="form-group">
            <label for="category">類別 *</label>
            <select id="category" name="category" required>
                <option value="subject" <?php echo $achievement['category'] === 'subject' ? 'selected' : ''; ?>>擅長科目</option>
                <option value="language" <?php echo $achievement['category'] === 'language' ? 'selected' : ''; ?>>程式語言</option>
                <option value="competition" <?php echo $achievement['category'] === 'competition' ? 'selected' : ''; ?>>參與競賽</option>
                <option value="certificate" <?php echo $achievement['category'] === 'certificate' ? 'selected' : ''; ?>>取得證照</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="title">成果名稱 *</label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo htmlspecialchars($achievement['title']); ?>">
        </div>
        
        <div class="form-group">
            <label for="description">詳細說明</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($achievement['description'] ?? ''); ?></textarea>
        </div>
        
        <button type="submit" class="btn">儲存變更</button>
        <!-- 更新取消連結路徑 -->
        <a href="student_achievements.php" class="btn btn-danger">取消</a>
    </form>
</div>

<?php include 'footer.php'; ?>
