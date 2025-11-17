<?php
require_once 'config.php';

// 檢查是否為學生
if (!isLoggedIn() || isAdmin()) {
    redirect('login.php');
}

$user_id = getCurrentUserId();
$success = '';
$error = '';

// 取得使用者資料
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 處理個人資料更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error = '姓名和電子郵件不能為空';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, bio = ? WHERE id = ?");
        if ($stmt->execute([$full_name, $email, $bio, $user_id])) {
            $success = '個人資料更新成功';
            $_SESSION['full_name'] = $full_name;
            // 重新載入使用者資料
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } else {
            $error = '更新失敗，請稍後再試';
        }
    }
}

// 處理照片上傳
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_photo'])) {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (!in_array(strtolower($filetype), $allowed)) {
            $error = '只允許上傳 JPG, PNG, GIF 格式的圖片';
        } else {
            // 建立上傳目錄
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // 產生唯一檔名
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                // 更新資料庫
                $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
                if ($stmt->execute([$new_filename, $user_id])) {
                    $success = '照片上傳成功';
                    // 重新載入使用者資料
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                }
            } else {
                $error = '照片上傳失敗';
            }
        }
    } else {
        $error = '請選擇要上傳的照片';
    }
}

$pageTitle = '我的資料 - 學生學習成果認證系統';
include 'header.php';
?>

<h2>我的個人資料</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="card">
    <h3>個人照片</h3>
    
    <?php if (!empty($user['photo'])): ?>
        <!-- 更新照片路徑 -->
        <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
             alt="個人照片" class="profile-photo">
    <?php else: ?>
        <p style="color: #666;">尚未上傳照片</p>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="photo">上傳新照片</label>
            <input type="file" id="photo" name="photo" accept="image/*">
        </div>
        <button type="submit" name="upload_photo" class="btn">上傳照片</button>
    </form>
</div>

<div class="card">
    <h3>基本資料</h3>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">使用者名稱（不可修改）</label>
            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        </div>
        
        <div class="form-group">
            <label for="full_name">真實姓名 *</label>
            <input type="text" id="full_name" name="full_name" required
                   value="<?php echo htmlspecialchars($user['full_name']); ?>">
        </div>
        
        <div class="form-group">
            <label for="email">電子郵件 *</label>
            <input type="email" id="email" name="email" required
                   value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        
        <div class="form-group">
            <label for="bio">個人簡介</label>
            <textarea id="bio" name="bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
        </div>
        
        <button type="submit" name="update_profile" class="btn">儲存資料</button>
    </form>
</div>

<?php include 'footer.php'; ?>
