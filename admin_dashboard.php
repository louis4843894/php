<?php
$pageTitle = '管理後台 - 學生學習成果認證系統';
session_start();
require_once 'config.php';

// 檢查是否為管理員
if (!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit;
}

// 取得詳細統計
$stats = [];

// 總學生數
$sql = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
$stmt = $pdo->query($sql);
$stats['total_students'] = $stmt->fetch()['count'];

// 總成果數
$sql = "SELECT COUNT(*) as count FROM achievements";
$stmt = $pdo->query($sql);
$stats['total_achievements'] = $stmt->fetch()['count'];

// 待審核成果數
$sql = "SELECT COUNT(*) as count FROM achievements WHERE status = 'pending'";
$stmt = $pdo->query($sql);
$stats['pending'] = $stmt->fetch()['count'];

// 已認證成果數
$sql = "SELECT COUNT(*) as count FROM achievements WHERE status = 'approved'";
$stmt = $pdo->query($sql);
$stats['approved'] = $stmt->fetch()['count'];

// 不通過成果數
$sql = "SELECT COUNT(*) as count FROM achievements WHERE status = 'rejected'";
$stmt = $pdo->query($sql);
$stats['rejected'] = $stmt->fetch()['count'];

// 各類別成果統計
$sql = "SELECT category, COUNT(*) as count FROM achievements WHERE status = 'approved' GROUP BY category";
$stmt = $pdo->query($sql);
$category_stats = $stmt->fetchAll();

// 最新待審核成果
$sql = "SELECT a.*, u.full_name, u.username 
        FROM achievements a
        JOIN users u ON a.user_id = u.id
        WHERE a.status = 'pending'
        ORDER BY a.created_at DESC
        LIMIT 10";
$stmt = $pdo->query($sql);
$pending_achievements = $stmt->fetchAll();

include 'header.php';
?>

<div class="card">
    <h2>管理後台總覽</h2>
    <p>歡迎回來，<?php echo htmlspecialchars($_SESSION['username']); ?> 管理員！</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $stats['total_students']; ?></h3>
        <p>總學生數</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['total_achievements']; ?></h3>
        <p>總成果數</p>
    </div>
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

<?php if (!empty($category_stats)): ?>
<div class="card">
    <h2>各類別已認證成果統計</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>類別</th>
                    <th>數量</th>
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
                foreach ($category_stats as $stat): 
                ?>
                <tr>
                    <td><?php echo $categories[$stat['category']]; ?></td>
                    <td><strong><?php echo $stat['count']; ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($pending_achievements)): ?>
<div class="card">
    <h2>最新待審核成果</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>學生</th>
                    <th>類別</th>
                    <th>成果名稱</th>
                    <th>提交時間</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_achievements as $achievement): ?>
                <tr>
                    <td><?php echo htmlspecialchars($achievement['full_name']); ?></td>
                    <td>
                        <?php
                        $categories = [
                            'subject' => '擅長科目',
                            'language' => '程式語言',
                            'competition' => '參與競賽',
                            'certificate' => '取得證照'
                        ];
                        echo $categories[$achievement['category']];
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($achievement['created_at'])); ?></td>
                    <td>
                        <a href="admin_review.php?id=<?php echo $achievement['id']; ?>" class="btn btn-small btn-warning">審核</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top: 15px;">
        <a href="admin_review.php" class="btn">查看所有待審核成果</a>
    </div>
</div>
<?php else: ?>
<div class="card">
    <h2>待審核成果</h2>
    <p style="color: #b0b0b0; text-align: center; padding: 20px;">目前沒有待審核的成果</p>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
