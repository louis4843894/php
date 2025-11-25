<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <!-- 響應式設計設定 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? '學生學習成果認證系統'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            /* width / height 會包含 padding 和 border 在內一起算 */
            box-sizing: border-box;
        }
        
        body {
            /* 字型備用清單，瀏覽器會「照順序」找能用的字體 */
            font-family: 'Microsoft JhengHei', 'PingFang TC', 'Segoe UI', Arial, sans-serif;
            /* 行高 */
            line-height: 1.6;
            /* 黑金配色背景 */
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            color: #e0e0e0;
            min-height: 100vh;
        }
        /* 中心容器 */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* 重新設計標題與導覽列為一體化設計 */
        header {
            background: linear-gradient(180deg, #000000 0%, #1a1a1a 100%);
            /* box-shadow(矩形): offset-x(水平位移) offset-y(垂直位移) blur-radius(模糊半徑) color; */
            box-shadow: 0 4px 30px rgba(212, 175, 55, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            /* 把這個元素變成 flex 容器，它的「直接子元素」自動變成一排，ex:左右兩邊各一個區塊那種版面*/
            display: flex;
            align-items: center;
            /* 第一個子元素：靠最左邊，最後一個子元素：靠最右邊，中間的子元素：把剩下空間平均分配在「元素與元素之間」*/
            justify-content: space-between;
            padding: 1.2rem 2rem;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            /* flex 或 grid 的子元素彼此之間保持 15px 的間距 */
            gap: 15px;
        }
        
        .logo-icon {
            font-size: 2.2rem;
            /* drop-shadow(圖形不透明部分的輪廓)(offset-x offset-y blur-radius color) */
            filter: drop-shadow(0 0 10px rgba(212, 175, 55, 0.6));
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffd700 0%, #d4af37 50%, #ffd700 100%);
            /* 讓「背景」只出現在文字本身上，而不是整個盒子 */
            -webkit-background-clip: text;
            background-clip: text;
            /* 把文字本來的顏色變成透明 */
            -webkit-text-fill-color: transparent;
            /* 字距 */
            letter-spacing: 1px;
        }
        
        /* 導覽列整合在同一行 */
        nav {
            background: transparent;
            padding: 0;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        nav ul li a {
            color: #b8975a;
            /* 去底線 */
            text-decoration: none;
            padding: 10px 20px;
            /* 把元素變成區塊元素 */
            display: block;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
            border-radius: 6px;
            position: relative;
        }
        /* 背景 + 文字變亮 */
        nav ul li a:hover {
            background: rgba(212, 175, 55, 0.15);
            color: #ffd700;
        }
        
        nav ul li a::after {
            /* ::before ::after 這兩個偽元素 預設是不存在的只是一個「可以生成的虛擬盒子」
            只有加了content: '';，瀏覽器才會讓它變成真正的元素盒子（box），可以設定CSS*/
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #ffd700, transparent);
            transition: width 0.3s ease;
        }
        
        nav ul li a:hover::after {
            width: 80%;
        }
        
        /* 使用者名稱樣式 */
        .username {
            color: #d4af37;
            font-weight: 600;
            padding: 8px 16px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 20px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            font-size: 14px;
        }
        
        /* 響應式：小螢幕時改為堆疊 */
        @media (max-width: 768px) {
            .header-content {
                /* 原本如果是左右排（row），小螢幕就變成上下堆疊 */
                flex-direction: column;
                gap: 15px;
                padding: 1rem;
            }
            
            .logo-text {
                font-size: 1.2rem;
            }
            
            nav ul {
                /* 允許選單項目「自動換行」 */
                flex-wrap: wrap;
                justify-content: center;
                gap: 3px;
            }
            
            nav ul li a {
                padding: 8px 12px;
                font-size: 13px;
            }
        }
        /* 訊息框樣式 */
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .alert-success {
            background-color: #1a3a1a;
            color: #7fff7f;
            border-left-color: #4caf50;
        }
        
        .alert-error {
            background-color: #3a1a1a;
            color: #ff7f7f;
            border-left-color: #f44336;
        }
        
        /* 最新認證成果、系統功能說明 */
        .card {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            border: 1px solid #3a3a3a;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(212, 175, 55, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(212, 175, 55, 0.3);
            transform: translateY(-2px);
        }
        
        .card h2, .card h3 {
            color: #d4af37;
            margin-bottom: 15px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 10px;
        }
        /* 登入表單 */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #d4af37;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #3a3a3a;
            border-radius: 6px;
            font-size: 14px;
            background: #0a0a0a;
            color: #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }
        
        /* 按鈕金色風格 */
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #d4af37 0%, #f4e5a7 100%);
            color: #000;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
            margin-right: 10px;
            margin-bottom: 10px;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #ffd700 0%, #d4af37 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #4a4a4a 0%, #2d2d2d 100%);
            color: #d4af37;
            border: 2px solid #d4af37;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a5a5a 0%, #3d3d3d 100%);
            border-color: #ffd700;
            color: #ffd700;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #c62828 0%, #d32f2f 100%);
            color: #fff;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #b71c1c 0%, #c62828 100%);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #388e3c 0%, #4caf50 100%);
            color: #fff;
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #2e7d32 0%, #388e3c 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%);
            color: #000;
            font-weight: 600;
        }
        
        .btn-warning:hover {
            background: linear-gradient(135deg, #e65100 0%, #f57c00 100%);
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 13px;
        }
        
        /* 表格黑金風格 */
        .table-container {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a1a;
        }
        
        table th,
        table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #3a3a3a;
        }
        
        table th {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
            font-weight: 600;
            color: #d4af37;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
            border-bottom: 2px solid #d4af37;
        }
        
        table tr:hover {
            background: rgba(212, 175, 55, 0.05);
        }
        
        table td {
            color: #e0e0e0;
        }
        
        /* 狀態徽章金色系 */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .badge-pending {
            background: linear-gradient(135deg, #f57c00 0%, #ff9800 100%);
            color: #000;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        }
        
        .badge-approved {
            background: linear-gradient(135deg, #388e3c 0%, #4caf50 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }
        
        .badge-rejected {
            background: linear-gradient(135deg, #c62828 0%, #d32f2f 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(211, 47, 47, 0.3);
        }
        
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 10px 0;
            border: 4px solid #d4af37;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.4);
        }
        
        .profile-photo-small {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #d4af37;
        }
        
        /* 搜尋框金色強調 */
        .search-box {
            background: #1a1a1a;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 2px solid #d4af37;
            box-shadow: 0 4px 20px rgba(212, 175, 55, 0.2);
        }
        
        .search-box h3 {
            color: #d4af37;
            margin-bottom: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid #d4af37;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
        }
        
        .stat-card h3 {
            color: #d4af37;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #b0b0b0;
            font-size: 14px;
        }
        
        .welcome-section {
            text-align: center;
            padding: 40px 20px;
        }
        
        .welcome-section h2 {
            color: #d4af37;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .welcome-section p {
            font-size: 1.1rem;
            color: #b0b0b0;
            margin-bottom: 30px;
        }
        
        /* 響應式設計 */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                font-size: 12px;
            }
            
            table th,
            table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- 整合標題與導覽列為一體化設計 -->
    <header>
        <div class="header-content">
            <div class="logo-section">
                <span class="logo-icon">🏆</span>
                <h1 class="logo-text">學生學習成果認證系統</h1>
            </div>
            
            <nav>
                <ul>
                    <li><a href="index.php">首頁</a></li>
                    <li><a href="search.php">人才搜尋</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin_dashboard.php">管理後台</a></li>
                            <li><a href="admin_review.php">審核成果</a></li>
                        <?php else: ?>
                            <li><a href="student_profile.php">我的資料</a></li>
                            <li><a href="student_achievements.php">我的成果</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">登出</a></li>
                        <li><span class="username"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span></li>
                    <?php else: ?>
                        <li><a href="login.php">登入</a></li>
                        <li><a href="register.php">註冊</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <!-- </CHANGE> -->
    
    <div class="container">
        <?php
        // 顯示成功訊息
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        
        // 顯示錯誤訊息
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>
