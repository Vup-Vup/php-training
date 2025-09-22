<?php
session_start();

if (isset($_SESSION['id'])) {
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];

        // Xóa token trong Redis
        $redis = new Redis();
        $redis->connect('web-redis', 6379);
        $redis->del('login_token_' . $token);

        // Xóa cookie remember_token
        setcookie('remember_token', '', time() - 3600, "/");
    }

    // Xóa toàn bộ session
    session_unset();
    session_destroy();
}

// Xóa localStorage + redirect về login
echo "<script>
    localStorage.removeItem('login_token');
    window.location.href = 'login.php';
</script>";
exit;
