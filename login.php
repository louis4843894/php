<?php
require_once 'config.php';

// 如果已登入，導向首頁
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

// 處理登入表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = '請輸入使用者名稱和密碼';
    } else {
        // 查詢使用者
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && $password === $user['password']) {
            // 設定Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            // 導向適當的頁面
            if ($user['role'] === 'admin') {
                redirect('admin_review.php');
            } else {
                redirect('student_profile.php');
            }
        } else {
            $error = '使用者名稱或密碼錯誤';
        }
    }
}

$pageTitle = '登入 - 學生學習成果認證系統';
include 'header.php';
?>

<div class="card" style="max-width: 400px; margin: 0 auto;">
    <h2>系統登入</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">使用者名稱</label>
            <input type="text" id="username" name="username" required
                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">密碼</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn">登入</button>
        <a href="register.php" style="margin-left: 10px; color: aliceblue;">還沒有帳號？立即註冊</a>
    </form>
    
    <div style="margin-top: 20px; padding: 10px; background: #020202ff; border-radius: 4px;">
        <p><strong>測試帳號：</strong></p>
        <p>管理員 - 帳號: admin / 密碼: admin123</p>
        <p>學生 - 帳號: student1 / 密碼: student123</p>
    </div>
</div>

<?php include 'footer.php'; ?>
