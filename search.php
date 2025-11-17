<?php
session_start();
require_once 'config.php';

$pageTitle = '人才搜尋 - 學生學習成果認證系統';

$search_results = [];
$search_keyword = '';
$search_category = '';

if (isset($_GET['search'])) {
    $search_keyword = trim($_GET['keyword'] ?? '');
    $search_category = $_GET['category'] ?? '';
    
    // 建立查詢 - 以學生為單位分組
    $sql = "SELECT DISTINCT u.id, u.username, u.full_name, u.photo, u.bio,
            GROUP_CONCAT(DISTINCT a.title ORDER BY a.title SEPARATOR ', ') as achievements_list
            FROM users u
            INNER JOIN achievements a ON u.id = a.user_id
            WHERE u.role = 'student' AND a.status = 'approved'";
    
    $params = [];
    
    // 加入關鍵字搜尋
    if (!empty($search_keyword)) {
        $sql .= " AND (a.title LIKE ? OR a.description LIKE ? OR u.full_name LIKE ?)";
        $params[] = "%$search_keyword%";
        $params[] = "%$search_keyword%";
        $params[] = "%$search_keyword%";
    }
    
    // 加入類別篩選
    if (!empty($search_category)) {
        $sql .= " AND a.category = ?";
        $params[] = $search_category;
    }
    
    $sql .= " GROUP BY u.id ORDER BY u.full_name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $search_results = $stmt->fetchAll();
}

include 'header.php';
?>

<!-- 新增搜尋框樣式 -->
<div class="search-box">
    <h3>搜尋人才</h3>
    <form method="GET">
        <div class="form-group">
            <label>關鍵字搜尋</label>
            <input type="text" name="keyword" placeholder="搜尋技能、程式語言、證照或姓名..." value="<?php echo htmlspecialchars($search_keyword); ?>">
        </div>
        
        <div class="form-group">
            <label>類別篩選</label>
            <select name="category">
                <option value="">全部類別</option>
                <option value="subject" <?php echo $search_category === 'subject' ? 'selected' : ''; ?>>擅長科目</option>
                <option value="language" <?php echo $search_category === 'language' ? 'selected' : ''; ?>>程式語言</option>
                <option value="competition" <?php echo $search_category === 'competition' ? 'selected' : ''; ?>>參與競賽</option>
                <option value="certificate" <?php echo $search_category === 'certificate' ? 'selected' : ''; ?>>取得證照</option>
            </select>
        </div>
        
        <button type="submit" name="search" class="btn">搜尋</button>
        <a href="search.php" class="btn btn-secondary">清除</a>
    </form>
</div>

<?php if (isset($_GET['search'])): ?>
    <div class="card">
        <h2>搜尋結果</h2>
        <?php if (!empty($search_results)): ?>
            <p style="color: #b0b0b0; margin-bottom: 20px;">
                找到 <strong style="color: #d4af37;"><?php echo count($search_results); ?></strong> 位符合條件的學生
            </p>
            
            <!-- 以學生卡片方式顯示 -->
            <?php foreach ($search_results as $student): ?>
            <div class="card" style="margin-bottom: 20px; border: 2px solid #3a3a3a;">
                <div style="display: flex; align-items: start; gap: 20px;">
                    <?php if ($student['photo']): ?>
                        <img src="<?php echo htmlspecialchars($student['photo']); ?>" class="profile-photo-small" alt="個人照片">
                    <?php else: ?>
                        <div class="profile-photo-small" style="background: #3a3a3a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            👤
                        </div>
                    <?php endif; ?>
                    
                    <div style="flex: 1;">
                        <h3 style="color: #d4af37; margin-bottom: 10px;">
                            <?php echo htmlspecialchars($student['full_name']); ?>
                        </h3>
                        
                        <?php if ($student['bio']): ?>
                            <p style="color: #b0b0b0; margin-bottom: 10px;">
                                <?php echo htmlspecialchars($student['bio']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <p style="color: #e0e0e0;">
                            <strong style="color: #d4af37;">已認證技能：</strong>
                            <?php echo htmlspecialchars($student['achievements_list']); ?>
                        </p>
                        
                        <?php
                        // 取得該學生的詳細成果
                        $sql_details = "SELECT category, title, description 
                                       FROM achievements 
                                       WHERE user_id = ? AND status = 'approved'
                                       ORDER BY category, title";
                        $stmt_details = $pdo->prepare($sql_details);
                        $stmt_details->execute([$student['id']]);
                        $achievements = $stmt_details->fetchAll();
                        
                        if (!empty($achievements)):
                        ?>
                        <div style="margin-top: 15px;">
                            <?php
                            $categories = [
                                'subject' => '擅長科目',
                                'language' => '程式語言',
                                'competition' => '參與競賽',
                                'certificate' => '取得證照'
                            ];
                            
                            $grouped = [];
                            foreach ($achievements as $ach) {
                                $grouped[$ach['category']][] = $ach;
                            }
                            
                            foreach ($grouped as $cat => $items):
                            ?>
                            <div style="margin-bottom: 10px;">
                                <strong style="color: #d4af37;"><?php echo $categories[$cat]; ?>：</strong>
                                <?php foreach ($items as $item): ?>
                                    <span class="badge badge-approved"><?php echo htmlspecialchars($item['title']); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
        <?php else: ?>
            <p style="color: #b0b0b0; text-align: center; padding: 40px;">
                沒有找到符合條件的學生，請嘗試其他關鍵字或類別。
            </p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="card">
        <h2>使用說明</h2>
        <ul style="line-height: 2; color: #b0b0b0;">
            <li>在上方搜尋框輸入關鍵字（例如：PHP、MySQL、證照等）</li>
            <li>可以選擇特定類別進行篩選</li>
            <li>搜尋結果只會顯示「已認證」的學習成果</li>
            <li>點擊「搜尋」按鈕查看符合條件的學生</li>
        </ul>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
