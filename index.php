<?php
session_start();
require_once 'config.php';
$pageTitle = '首頁 - 學生學習成果認證系統';

$stats = [];

// 總學生數
$sql = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
$stmt = $pdo->query($sql);
$stats['students'] = $stmt->fetch()['count'];

// 已認證成果數
$sql = "SELECT COUNT(*) as count FROM achievements WHERE status = 'approved'";
$stmt = $pdo->query($sql);
$stats['approved'] = $stmt->fetch()['count'];

// 待審核成果數
$sql = "SELECT COUNT(*) as count FROM achievements WHERE status = 'pending'";
$stmt = $pdo->query($sql);
$stats['pending'] = $stmt->fetch()['count'];

include 'header.php';
?>

<!-- 新增歡迎區塊 -->
<div class="welcome-section">
    <h2>歡迎來到學生學習成果認證系統</h2>
</div>

<!-- 新增統計卡片 -->
<div class="stats-grid">
    <div class="stat-card">
        <h3><?php echo $stats['students']; ?></h3>
        <p>註冊學生</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['approved']; ?></h3>
        <p>已認證成果</p>
    </div>
    <div class="stat-card">
        <h3><?php echo $stats['pending']; ?></h3>
        <p>待審核成果</p>
    </div>
</div>

<?php
// 顯示最新認證的成果
$stmt = $pdo->prepare("
    SELECT a.*, u.full_name, u.username 
    FROM achievements a
    JOIN users u ON a.user_id = u.id
    WHERE a.status = 'approved'
    ORDER BY a.updated_at DESC
    LIMIT 5
");
$stmt->execute();
$recent = $stmt->fetchAll();
?>

<?php if (count($recent) > 0): ?>
<div class="card">
    <h2>最新認證成果</h2>
    <!-- 新增表格容器 -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>學生</th>
                    <th>類型</th>
                    <th>成果名稱</th>
                    <th>認證日期</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent as $item): ?>
                <tr>
                    <!-- 防XXS攻擊，htmlspecialchars把「有特殊意義的 HTML 符號」轉成「純文字顯示」 -->
                    <td><?php echo htmlspecialchars($item['full_name']); ?></td>
                    <td>
                        <?php
                        $categories = [
                            'subject' => '擅長科目',
                            'language' => '程式語言',
                            'competition' => '參與競賽',
                            'certificate' => '取得證照'
                        ];
                        echo $categories[$item['category']] ?? $item['category'];
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <!-- strtotime 將資料庫時間字串轉換為時間戳 -->
                    <td><?php echo date('Y-m-d', strtotime($item['updated_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- 新增功能說明區塊 -->
<div class="card">
    <h2>系統功能說明</h2>
    <h3>學生功能</h3>
    <ul style="line-height: 2;">
        <li>建立個人資料，上傳照片與簡介</li>
        <li>新增學習成果（擅長科目、程式語言、競賽、證照）</li>
        <li>管理自己的「待審核」成果（編輯/刪除）</li>
        <li>查看所有成果的認證狀態</li>
    </ul>
    
    <h3>管理員/教師功能</h3>
    <ul style="line-height: 2;">
        <li>查看所有待審核的學習成果</li>
        <li>審核成果（通過/不通過），並添加審核備註</li>
        <li>管理系統統計資料</li>
    </ul>
    
    <h3>訪客功能</h3>
    <ul style="line-height: 2;">
        <li>搜尋具備特定技能的學生</li>
        <li>只能看到「已認證」的成果</li>
        <li>不需登入即可使用搜尋功能</li>
    </ul>
</div>

<?php include 'footer.php'; ?>
