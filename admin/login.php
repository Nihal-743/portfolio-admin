<?php
define('NO_LOGIN_NEEDED', true);
require 'inc/boot.php';

if (is_admin()) go('index.php');

$error = '';

// first time on a fresh install there's no account yet, so we make one
$setup = (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0;

// lock the form for 10 min after 5 wrong tries
$tries = $_SESSION['login_tries'] ?? 0;
$lockedUntil = $_SESSION['login_locked'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $setup) {
    check_csrf();

    $username = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username)) {
        $error = 'Username should be 3-30 letters/numbers.';
    } elseif (strlen($pass) < 8) {
        $error = 'Password needs at least 8 characters.';
    } elseif ($pass !== ($_POST['confirm'] ?? '')) {
        $error = "Passwords don't match.";
    } else {
        db()->prepare('INSERT INTO users (username, password) VALUES (?, ?)')
            ->execute([$username, password_hash($pass, PASSWORD_DEFAULT)]);
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) db()->lastInsertId();
        $_SESSION['admin_name'] = $username;
        go('index.php');
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if ($lockedUntil > time()) {
        $error = 'Too many wrong tries. Try again in ' . ceil(($lockedUntil - time()) / 60) . ' min.';
    } else {
        $st = db()->prepare('SELECT * FROM users WHERE username = ?');
        $st->execute([trim($_POST['username'] ?? '')]);
        $user = $st->fetch();

        if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['username'];
            unset($_SESSION['login_tries'], $_SESSION['login_locked']);
            go('index.php');
        }

        $tries++;
        $_SESSION['login_tries'] = $tries;
        if ($tries >= 5) {
            $_SESSION['login_locked'] = time() + 600;
            $_SESSION['login_tries'] = 0;
        }
        $error = 'Wrong username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Login · Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__ . '/admin.css') ?>">
</head>
<body class="login-page">

  <form method="post" class="login-box">
    <a href="../" class="brand"><b>nihal<i>.</i></b> <span>admin</span></a>
    <?php if ($setup): ?>
      <h1>Create your login</h1>
      <p class="muted">First time here. Pick a username and password for the admin panel.</p>
    <?php else: ?>
      <h1>Welcome back</h1>
      <p class="muted">Log in to edit your portfolio and see who visited.</p>
    <?php endif; ?>

    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

    <?= csrf_field() ?>
    <div class="field">
      <label for="u">Username</label>
      <input id="u" name="username" type="text" autocomplete="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
    </div>
    <div class="field">
      <label for="p">Password</label>
      <input id="p" name="password" type="password" autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>" required>
    </div>
    <?php if ($setup): ?>
      <div class="field">
        <label for="c">Confirm password</label>
        <input id="c" name="confirm" type="password" autocomplete="new-password" required>
      </div>
      <button class="btn btn-lime btn-block">Create account</button>
    <?php else: ?>
      <button class="btn btn-lime btn-block">Log in</button>
    <?php endif; ?>
  </form>

</body>
</html>
