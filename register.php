<?php
require_once 'config.php';

// 如果已登入，導向首頁
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

// 處理註冊表單
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    
    // 驗證欄位
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error = '所有欄位都必須填寫';
    } elseif (strlen($username) < 4) {
        $error = '使用者名稱至少需要4個字元';
    } elseif (strlen($password) < 6) {
        $error = '密碼至少需要6個字元';
    } elseif ($password !== $confirm_password) {
        $error = '兩次密碼輸入不一致';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '電子郵件格式不正確';
    } else {
        // 檢查使用者名稱是否已存在
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = '此使用者名稱已被使用';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, email, full_name, role) 
                VALUES (?, ?, ?, ?, 'student')
            ");
            
            if ($stmt->execute([$username, $password, $email, $full_name])) {
                $success = '註冊成功！請登入';
            } else {
                $error = '註冊失敗，請稍後再試';
            }
        }
    }
}

$pageTitle = '註冊 - 學生學習成果認證系統';
include 'header.php';
?>

<div class="card" style="max-width: 500px; margin: 0 auto;">
    <h2>學生註冊</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
            <a href="login.php" style="color: aliceblue;">立即登入</a>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">使用者名稱 *</label>
            <input type="text" id="username" name="username" required 
                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="full_name">真實姓名 *</label>
            <input type="text" id="full_name" name="full_name" required
                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="email">電子郵件 *</label>
            <input type="email" id="email" name="email" required
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">密碼 * (至少6個字元)</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">確認密碼 *</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn">註冊</button>
        <a href="login.php" style="margin-left: 10px; color: aliceblue;">已有帳號？立即登入</a>
    </form>
</div>

<?php include 'footer.php'; ?>
