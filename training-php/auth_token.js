// Lưu token vào localStorage
function saveToken(token) {
    localStorage.setItem('login_token', token);
}

// Lấy token từ localStorage
function getToken() {
    return localStorage.getItem('login_token');
}

// Xóa token khỏi localStorage
function removeToken() {
    localStorage.removeItem('login_token');
}

// Gửi token lên server để tự động đăng nhập
function autoLogin() {
    const token = getToken();
    if (token) {
        fetch('auto_login.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({token: token})
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                location.reload(); // Reload để tải lại trang với session đã login
            } else {
                removeToken(); // Token không hợp lệ thì xóa token
            }
        })
        .catch(() => {
            // Lỗi mạng hoặc server không phản hồi
            removeToken();
        });
    }
}

// Gọi autoLogin khi trang được load
window.addEventListener('DOMContentLoaded', autoLogin);
