<?php
// Start the session
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// CSRF check for POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['csrf-token']) ||
        $_POST['csrf-token'] !== $_SESSION['csrf-token']
    ) {
        die('Invalid CSRF token');
    }
}

$user = NULL; //Add new user
$_id = NULL;

if (!empty($_POST['id'])) {
    $_id = $_POST['id'];
    $user = $userModel->findUserById($_id);//Update existing user
}


if (!empty($_POST['submit'])) {
    if (!empty($_id)) {
        $userModel->updateUser($_POST);
    } else {
        $newId = $userModel->insertUser($_POST);

        $token = bin2hex(random_bytes(16));
        $redis = new Redis();
        $redis->connect('web-redis', 6379);
        $redis->setex('login_token_' . $token, 7 * 24 * 3600, $newId);

        $_SESSION['new_token'] = $token;
    }

    header('Location: list_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">

            <?php if ($user || !isset($_id)) { ?>
                <div class="alert alert-warning" role="alert">
                    User form
                </div>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($_id, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="csrf-token" value="<?php echo htmlspecialchars($_SESSION['csrf-token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input class="form-control" name="name" placeholder="Name" value="<?php if (!empty($user[0]['name'])) echo htmlspecialchars($user[0]['name'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Password">
                    </div>

                    <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                </form>
            <?php } else { ?>
                <div class="alert alert-success" role="alert">
                    User not found!
                </div>
            <?php } ?>
    </div>
</body>
</html>