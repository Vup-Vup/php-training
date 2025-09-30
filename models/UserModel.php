<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel
{

    public function findUserById($id)
    {
        $sql = 'SELECT * FROM users WHERE id = ?';
        $rows = $this->selectPrepared($sql, 'i', [$id]);
        return !empty($rows) ? $rows[0] : null;
    }

    public function findUser($keyword)
    {
        // search in name or email (adjust column names to your schema)
        $sql = 'SELECT * FROM users WHERE name LIKE ? OR email LIKE ?';
        $like = '%' . $keyword . '%';
        return $this->selectPrepared($sql, 'ss', [$like, $like]);
    }

    public function auth($userName, $password)
    {
        $sql = 'SELECT * FROM users WHERE name = ? LIMIT 1';
        $rows = $this->selectPrepared($sql, 's', [$userName]);

        if (empty($rows)) {
            error_log("User not found: " . $userName);
            return null;
        }

        $user = $rows[0];
        error_log("Found user: " . print_r($user, true));

        // Kiểm tra mật khẩu bằng md5
        if (isset($user['password']) && md5($password) === $user['password']) {
            error_log("Password matched (md5)");
            return $user; // Mật khẩu đúng
        } else {
            error_log("Password does not match (md5)");
        }
        return null; // Nếu không khớp mật khẩu
    }


    public function deleteUserById($id)
    {
        $sql = 'DELETE FROM users WHERE id = ?';
        return $this->executePrepared($sql, 'i', [$id]);
    }

    public function updateUser($input)
    {
        // ensure id exists
        $sql = 'UPDATE users SET name = ?, password = ? WHERE id = ?';
        // Hash password bằng md5
        $hashed = md5($input['password']);
        return $this->executePrepared($sql, 'ssi', [$input['name'], $hashed, $input['id']]);
    }

    public function insertUser($input)
    {
        $sql = 'INSERT INTO users (`name`, `password`) VALUES (?, ?)';
        $hashed = md5($input['password']);
        return $this->executePrepared($sql, 'ss', [$input['name'], $hashed]);
    }

    public function getUsers($params = [])
    {
        if (!empty($params['keyword'])) {
            // Use prepared statement to avoid injection and avoid multi_query
            $sql = 'SELECT * FROM users WHERE name LIKE ?';
            $like = '%' . $params['keyword'] . '%';
            $users = $this->selectPrepared($sql, 's', [$like]);
        } else {
            $sql = 'SELECT * FROM users';
            $users = $this->select($sql);
        }
        return $users;
    }
}
