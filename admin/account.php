<?php
require 'inc/boot.php';

$id = (int) $_SESSION['admin_id'];
$st = db()->prepare('SELECT * FROM users WHERE id = ?');
$st->execute([$id]);
$user = $st->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $username = trim($_POST['username'] ?? '');
    $current  = $_POST['current'] ?? '';
    $new      = $_POST['new'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!password_verify($current, $user['password'])) {
        flash('Current password is wrong.', 'error');
    } elseif ($username === '' || !preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username)) {
        flash('Username should be 3-30 letters/numbers.', 'error');
    } elseif ($new !== '' && strlen($new) < 8) {
        flash('New password needs at least 8 characters.', 'error');
    } elseif ($new !== $confirm) {
        flash("New passwords don't match.", 'error');
    } else {
        $hash = $new !== '' ? password_hash($new, PASSWORD_DEFAULT) : $user['password'];
        db()->prepare('UPDATE users SET username = ?, password = ? WHERE id = ?')->execute([$username, $hash, $id]);
        $_SESSION['admin_name'] = $username;
        flash('Account updated.');
    }
    go('account.php');
}

admin_header('Account', 'account');
?>

<form method="post" class="card form-card narrow">
  <?= csrf_field() ?>
  <h2>Login details</h2>
  <div class="fields">
    <div class="field">
      <label for="username">Username</label>
      <input id="username" name="username" value="<?= e($user['username']) ?>" required autocomplete="username">
    </div>
    <div class="field">
      <label for="new">New password</label>
      <input id="new" type="password" name="new" autocomplete="new-password">
      <small class="hint">Leave empty to keep the same password. Min 8 characters.</small>
    </div>
    <div class="field">
      <label for="confirm">Confirm new password</label>
      <input id="confirm" type="password" name="confirm" autocomplete="new-password">
    </div>
    <div class="field">
      <label for="current">Current password</label>
      <input id="current" type="password" name="current" required autocomplete="current-password">
      <small class="hint">Needed to save any change here.</small>
    </div>
  </div>
  <div class="form-actions">
    <button class="btn btn-lime">Update account</button>
  </div>
</form>

<?php admin_footer(); ?>
