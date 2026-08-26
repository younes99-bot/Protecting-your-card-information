<?php
session_start();

// كلمة السر لادخل اللوحة (يمكنك تغييرها)
$admin_password = "admin123";

if (isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['loggedin'] = true;
    } else {
        $error = "كلمة المرور غير صحيحة!";
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; padding: 40px; text-align: center; }
        .card { background: white; padding: 30px; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        input[type="password"] { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .error { color: red; margin-bottom: 10px; }
        a { color: #d9534f; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <h2>مرحباً بك في لوحة التحكم</h2>
        <p>النظام يعمل بنجاح عبر Render!</p>
        <hr>
        <a href="admin.php?action=logout">تسجيل الخروج</a>
    <?php else: ?>
        <h2>تسجيل الدخول للإدارة</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="أدخل كلمة المرور" required>
            <button type="submit">دخول</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
