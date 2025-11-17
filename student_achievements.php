<?php
require_once 'config.php';

// 檢查是否為學生
if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$user_id = getCurrentUserId();
$success = '';
$error = '';

// 處理新增成果
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_achievement'])) {
    $category = $_POST['category'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($category) || empty($title)) {
        $error = '類別和成果名稱不能為空';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO achievements (user_id, category, title, description, status) 
            VALUES (?, ?, ?, ?, 'pending')
        ");
        if ($stmt->execute([$user_id, $category, $title, $description])) {
            $success = '成果新增成功，等待審核中';
        } else {
            $error = '新增失敗，請稍後再試';
        }
    }
}

// 處理編輯成果
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_achievement'])) {
    $achievement_id = $_POST['achievement_id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // 確認這是使用者自己的待審核項目
    $stmt = $pdo->prepare("
        SELECT id FROM achievements 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->execute([$achievement_id, $user_id]);
    
    if (!$stmt->fetch()) {
        $error = '無法編輯此項目';
    } elseif (empty($title)) {
        $error = '成果名稱不能為空';
    } else {
        $stmt = $pdo->prepare("
            UPDATE achievements 
            SET title = ?, description = ? 
            WHERE id = ? AND user_id = ? AND status = 'pending'
        ");
        if ($stmt->execute([$title, $description, $achievement_id, $user_id])) {
            $success = '成果更新成功';
        } else {
            $error = '更新失敗，請稍後再試';
        }
    }
}

// 處理刪除成果
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $achievement_id = $_GET['delete'];
    
    // 確認這是使用者自己的待審核項目
    $stmt = $pdo->prepare("
        DELETE FROM achievements 
        WHERE id = ? AND user_id = ? AND status = 'pending'
    ");
    if ($stmt->execute([$achievement_id, $user_id])) {
        $success = '成果已刪除';
    } else {
        $error = '無法刪除此項目（只能刪除待審核的項目）';
    }
}

// 取得使用者的所有成果
$stmt = $pdo->prepare("
    SELECT * FROM achievements 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$achievements = $stmt->fetchAll();

// 統計數量
$stats = [
    'total' => count($achievements),
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];

foreach ($achievements as $item) {
    $stats[$item['status']]++;
}

$pageTitle = '我的成果 - 學生學習成果認證系統';
include 'header.php';
?>

<h2>我的學習成果</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- 統計資訊 -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
    <div class="card" style="text-align: center;">
        <h3 style="color: #007bff;"><?php echo $stats['total']; ?></h3>
        <p>總成果數</p>
    </div>
    <div class="card" style="text-align: center;">
        <h3 style="color: #ffc107;"><?php echo $stats['pending']; ?></h3>
        <p>待審核</p>
    </div>
    <div class="card" style="text-align: center;">
        <h3 style="color: #28a745;"><?php echo $stats['approved']; ?></h3>
        <p>已認證</p>
    </div>
    <div class="card" style="text-align: center;">
        <h3 style="color: #dc3545;"><?php echo $stats['rejected']; ?></h3>
        <p>不通過</p>
    </div>
</div>

<!-- 新增成果表單 -->
<div class="card">
    <h3>➕ 新增成果</h3>
    <form method="POST" action="">
        <div class="form-group">
            <label for="category">類別 *</label>
            <select id="category" name="category" required>
                <option value="">請選擇類別</option>
                <option value="subject">擅長科目</option>
                <option value="language">程式語言</option>
                <option value="competition">參與競賽</option>
                <option value="certificate">取得證照</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="title">成果名稱 *</label>
            <input type="text" id="title" name="title" required 
                   placeholder="例如：PHP程式設計、資訊安全技能競賽、TQC網頁設計認證">
        </div>
        
        <div class="form-group">
            <label for="description">詳細說明</label>
            <textarea id="description" name="description" 
                      placeholder="描述您的成果內容、獲得的成績或心得"></textarea>
        </div>
        
        <button type="submit" name="add_achievement" class="btn">新增成果</button>
    </form>
</div>

<!-- 成果列表 -->
<div class="card">
    <h3>成果列表</h3>
    
    <?php if (count($achievements) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>類別</th>
                    <th>成果名稱</th>
                    <th>狀態</th>
                    <th>建立日期</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $categories = [
                    'subject' => '擅長科目',
                    'language' => '程式語言',
                    'competition' => '參與競賽',
                    'certificate' => '取得證照'
                ];
                
                $status_labels = [
                    'pending' => '待審核',
                    'approved' => '已認證',
                    'rejected' => '不通過'
                ];
                
                foreach ($achievements as $item): 
                ?>
                <tr>
                    <td><?php echo $categories[$item['category']] ?? $item['category']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                        <?php if (!empty($item['description'])): ?>
                            <br><small style="color: #666;"><?php echo htmlspecialchars(mb_substr($item['description'], 0, 50)); ?><?php echo mb_strlen($item['description']) > 50 ? '...' : ''; ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $item['status']; ?>">
                            <?php echo $status_labels[$item['status']] ?? $item['status']; ?>
                        </span>
                    </td>
                    <td><?php echo date('Y-m-d', strtotime($item['created_at'])); ?></td>
                    <td>
                        <?php if ($item['status'] === 'pending'): ?>
                            <!-- 更新編輯和刪除連結路徑 -->
                            <a href="student_edit_achievement.php?id=<?php echo $item['id']; ?>" class="btn" style="padding: 5px 10px; font-size: 12px;">編輯</a>
                            <a href="?delete=<?php echo $item['id']; ?>" 
                               onclick="return confirm('確定要刪除此項目嗎？');"
                               class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">刪除</a>
                        <?php else: ?>
                            <span style="color: #999; font-size: 12px;">無法編輯</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; padding: 20px; color: #666;">尚未新增任何成果</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
