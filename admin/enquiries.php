<?php
require 'inc/boot.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int) ($_POST['id'] ?? 0);

    if (isset($_POST['delete'])) {
        db()->prepare('DELETE FROM enquiries WHERE id = ?')->execute([$id]);
        flash('Enquiry deleted.');
        go('enquiries.php');
    }
    if (isset($_POST['unread'])) {
        db()->prepare('UPDATE enquiries SET is_read = 0 WHERE id = ?')->execute([$id]);
        flash('Marked as unread.');
        go('enquiries.php');
    }
    if (isset($_POST['read_all'])) {
        db()->exec('UPDATE enquiries SET is_read = 1');
        flash('All marked as read.');
        go('enquiries.php');
    }
}

// single enquiry
if (!empty($_GET['view'])) {
    $st = db()->prepare('SELECT * FROM enquiries WHERE id = ?');
    $st->execute([(int) $_GET['view']]);
    $q = $st->fetch();

    if (!$q) go('enquiries.php');

    if (!$q['is_read']) {
        db()->prepare('UPDATE enquiries SET is_read = 1 WHERE id = ?')->execute([$q['id']]);
    }

    $isEmail = filter_var($q['contact'], FILTER_VALIDATE_EMAIL);
    $digits = preg_replace('/\D/', '', $q['contact']);
    if (strlen($digits) === 10) $digits = '91' . $digits;

    admin_header('Enquiry', 'enquiries');
    ?>
    <a href="enquiries.php" class="back">← All enquiries</a>

    <div class="card enq-detail">
      <div class="enq-top">
        <span class="avatar big"><?= e(strtoupper(mb_substr($q['name'], 0, 1))) ?></span>
        <div>
          <h2><?= e($q['name']) ?></h2>
          <p class="muted"><?= $q['company'] ? e($q['company']) . ' · ' : '' ?><?= date('d M Y, h:i A', strtotime($q['created_at'])) ?></p>
        </div>
      </div>

      <dl class="enq-info">
        <dt>Contact</dt>
        <dd><?= e($q['contact']) ?></dd>
      </dl>

      <div class="enq-msg"><?= nl2br(e($q['message'])) ?></div>

      <div class="form-actions">
        <?php if ($isEmail): ?>
          <a href="mailto:<?= e($q['contact']) ?>?subject=<?= rawurlencode('Re: your message on my portfolio') ?>" class="btn btn-lime">Reply by email</a>
        <?php elseif (strlen($digits) >= 10): ?>
          <a href="https://wa.me/<?= $digits ?>" target="_blank" class="btn btn-lime">Reply on WhatsApp</a>
          <a href="tel:+<?= $digits ?>" class="btn btn-ghost">Call</a>
        <?php endif; ?>

        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $q['id'] ?>">
          <button name="unread" value="1" class="btn btn-ghost">Mark unread</button>
        </form>
        <form method="post" onsubmit="return confirm('Delete this enquiry?')">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= $q['id'] ?>">
          <button name="delete" value="1" class="btn btn-danger">Delete</button>
        </form>
      </div>
    </div>
    <?php
    admin_footer();
    exit;
}

// list
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $st = db()->prepare('SELECT * FROM enquiries WHERE name LIKE ? OR company LIKE ? OR contact LIKE ? OR message LIKE ? ORDER BY id DESC');
    $like = '%' . $search . '%';
    $st->execute([$like, $like, $like, $like]);
    $rows = $st->fetchAll();
} else {
    $rows = db()->query('SELECT * FROM enquiries ORDER BY id DESC')->fetchAll();
}

admin_header('Enquiries', 'enquiries');
?>

<div class="page-head">
  <form method="get" class="search">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search name, company, message...">
  </form>
  <?php if (unread_count()): ?>
    <form method="post">
      <?= csrf_field() ?>
      <button name="read_all" value="1" class="btn btn-ghost">Mark all as read</button>
    </form>
  <?php endif; ?>
</div>

<div class="card list">
  <?php if (!$rows): ?>
    <p class="empty"><?= $search ? 'No enquiries match "' . e($search) . '".' : 'No enquiries yet. They will show up here when someone fills the contact form.' ?></p>
  <?php endif; ?>

  <?php foreach ($rows as $q): ?>
    <a href="?view=<?= $q['id'] ?>" class="list-row enq-row<?= $q['is_read'] ? '' : ' unread' ?>">
      <span class="avatar"><?= e(strtoupper(mb_substr($q['name'], 0, 1))) ?></span>
      <div class="list-main">
        <strong><?= e($q['name']) ?><?= $q['company'] ? ' <span class="muted">· ' . e($q['company']) . '</span>' : '' ?></strong>
        <span class="muted"><?= e(mb_strimwidth($q['message'], 0, 110, '...')) ?></span>
      </div>
      <span class="when"><?= time_ago($q['created_at']) ?></span>
    </a>
  <?php endforeach; ?>
</div>

<?php admin_footer(); ?>
