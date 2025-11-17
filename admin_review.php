<?php
session_start();
require_once 'config.php';

// 檢查是否為管理員
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review'])) {
    $achievement_id = (int)$_POST['achievement_id'];
    $new_status = $_POST['status'] ?? '';
    $review_note = trim($_POST['review_note'] ?? '');
    
    if (!in_array($new_status, ['approved', 'rejected'])) {
        $_SESSION['error'] = '無效的狀態';
    } else {
        $stmt = $pdo->prepare("UPDATE achievements 
                            SET status = ?, review_note = ?, reviewed_by = ?, reviewed_at = NOW() 
                            WHERE id = ?");
        if ($stmt->execute([$new_status, $review_note, $_SESSION['user_id'], $achievement_id])) {
            $status_text = $new_status === 'approved' ? '已認證' : '不通過';
            $_SESSION['success'] = "成果已標記為「{$status_text}」！";
        } else {
            $_SESSION['error'] = '審核失敗，請稍後再試';
        }
        header('Location: admin_review.php');
        exit;
    }
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT a.*, u.full_name, u.username, u.email
            FROM achievements a
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $achievement = $stmt->fetch();
    
    if (!$achievement) {
        $_SESSION['error'] = "找不到此成果！";
        header('Location: admin_review.php');
        exit;
    }
    
    $pageTitle = '審核成果 - 學生學習成果認證系統';
    include 'header.php';
    ?>
    
    <div class="card">
        <h2>審核學習成果</h2>
        
        <div style="margin-bottom: 20px;">
            <p><strong>學生姓名：</strong><?php echo htmlspecialchars($achievement['full_name']); ?> (<?php echo htmlspecialchars($achievement['username']); ?>)</p>
            <p><strong>Email：</strong><?php echo htmlspecialchars($achievement['email']); ?></p>
            <p><strong>類別：</strong>
                <?php
                $categories = [
                    'subject' => '擅長科目',
                    'language' => '程式語言',
                    'competition' => '參與競賽',
                    'certificate' => '取得證照'
                ];
                echo $categories[$achievement['category']];
                ?>
            </p>
            <p><strong>成果名稱：</strong><?php echo htmlspecialchars($achievement['title']); ?></p>
            <p><strong>描述：</strong></p>
            <div style="background: #0a0a0a; padding: 15px; border-radius: 6px; border-left: 4px solid #d4af37;">
                <?php echo nl2br(htmlspecialchars($achievement['description'] ?: '(無描述)')); ?>
            </div>
            <p style="margin-top: 15px;"><strong>提交時間：</strong><?php echo date('Y-m-d H:i:s', strtotime($achievement['created_at'])); ?></p>
            <p><strong>目前狀態：</strong>
                <?php
                $status_badges = [
                    'pending' => '<span class="badge badge-pending">待審核</span>',
                    'approved' => '<span class="badge badge-approved">已認證</span>',
                    'rejected' => '<span class="badge badge-rejected">不通過</span>'
                ];
                echo $status_badges[$achievement['status']];
                ?>
            </p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="achievement_id" value="<?php echo $achievement['id']; ?>">
            
            <div class="form-group">
                <label>審核結果</label>
                <select name="status" required>
                    <option value="">請選擇...</option>
                    <option value="approved">通過認證</option>
                    <option value="rejected">不通過</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>審核備註（選填）</label>
                <textarea name="review_note" placeholder="輸入審核意見或備註..."><?php echo htmlspecialchars($achievement['review_note'] ?? ''); ?></textarea>
            </div>
            
            <button type="submit" name="review" class="btn btn-success">送出審核</button>
            <a href="admin_review.php" class="btn btn-secondary">返回列表</a>
        </form>
    </div>
    
    <?php
    include 'footer.php';
    exit;
}

// 取得篩選條件
$filter_status = $_GET['filter'] ?? 'pending';
$allowed_filters = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($filter_status, $allowed_filters)) {
    $filter_status = 'pending';
}

// 建立查詢
$sql = "
    SELECT a.*, u.full_name, u.username, u.email
    FROM achievements a
    JOIN users u ON a.user_id = u.id
";

if ($filter_status !== 'all') {
    $sql .= " WHERE a.status = ?";
    $params = [$filter_status];
} else {
    $params = [];
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$achievements = $stmt->fetchAll();

// 統計數量
$stmt = $pdo->query("
    SELECT status, COUNT(*) as count 
    FROM achievements 
    GROUP BY status
");
$stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
while ($row = $stmt->fetch()) {
    $stats[$row['status']] = $row['count'];
}

$pageTitle = '審核成果 - 學生學習成果認證系統';
include 'header.php';
?>

<div class="card">
    <h2>審核學生成果</h2>
    <p>共有 <strong style="color: #d4af37;"><?php echo count($achievements); ?></strong> 筆成果符合篩選條件</p>
</div>

<!-- 統計資訊 -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $stats['pending']; ?></h3>
        <p>待審核</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['approved']; ?></h3>
        <p>已認證</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['rejected']; ?></h3>
        <p>不通過</p>
    </div>
</div>

<!-- 篩選選項 -->
<div class="card">
    <h3>篩選條件</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="?filter=pending" class="btn <?php echo $filter_status === 'pending' ? 'btn-warning' : 'btn-secondary'; ?>">
            待審核 (<?php echo $stats['pending']; ?>)
        </a>
        <a href="?filter=approved" class="btn <?php echo $filter_status === 'approved' ? 'btn-success' : 'btn-secondary'; ?>">
            已認證 (<?php echo $stats['approved']; ?>)
        </a>
        <a href="?filter=rejected" class="btn <?php echo $filter_status === 'rejected' ? 'btn-danger' : 'btn-secondary'; ?>">
            不通過 (<?php echo $stats['rejected']; ?>)
        </a>
        <a href="?filter=all" class="btn <?php echo $filter_status === 'all' ? '' : 'btn-secondary'; ?>">
            全部
        </a>
    </div>
</div>

<!-- 成果列表 -->
<div class="card">
    <h3>成果列表</h3>
    
    <?php if (count($achievements) > 0): ?>
        <!-- 新增表格容器 -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>學生</th>
                        <th>類別</th>
                        <th>成果名稱</th>
                        <th>說明</th>
                        <th>狀態</th>
                        <th>提交日期</th>
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
                        <td>
                            <strong><?php echo htmlspecialchars($item['full_name']); ?></strong>
                            <br><small style="color: #999;"><?php echo htmlspecialchars($item['username']); ?></small>
                        </td>
                        <td><?php echo $categories[$item['category']] ?? $item['category']; ?></td>
                        <td><strong><?php echo htmlspecialchars($item['title']); ?></strong></td>
                        <td>
                            <?php if (!empty($item['description'])): ?>
                                <?php echo htmlspecialchars(mb_substr($item['description'], 0, 50)); ?>
                                <?php echo mb_strlen($item['description']) > 50 ? '...' : ''; ?>
                            <?php else: ?>
                                <span style="color: #999;">無說明</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="badge badge-<?php echo $item['status']; ?>">
                                <?php echo $status_labels[$item['status']] ?? $item['status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d H:i', strtotime($item['created_at'])); ?></td>
                        <td>
                            <!-- 改為詳細審核按鈕 -->
                            <a href="admin_review.php?id=<?php echo $item['id']; ?>" class="btn btn-small btn-warning" style="text-align: center; white-space: nowrap;">
                                審核
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p style="text-align: center; padding: 40px; color: #b0b0b0;">沒有符合條件的成果</p>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
