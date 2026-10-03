<?php
require 'inc/boot.php';

$cvFile = ROOT . '/cv/Nihal_Resume.pdf';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $f = $_FILES['cv'] ?? null;

    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
        flash('Choose a PDF first.', 'error');
    } elseif ($f['error'] !== UPLOAD_ERR_OK) {
        flash('Upload failed. The file may be bigger than the server allows.', 'error');
    } elseif ($f['size'] > 10 * 1024 * 1024) {
        flash('PDF must be under 10 MB.', 'error');
    } elseif ((new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) !== 'application/pdf') {
        flash('That file is not a PDF.', 'error');
    } else {
        if (!is_dir(ROOT . '/cv')) mkdir(ROOT . '/cv', 0755, true);
        move_uploaded_file($f['tmp_name'], $cvFile);
        flash('New resume uploaded. The site is using it now.');
    }
    go('resume.php');
}

$views = db()->query('SELECT * FROM cv_views ORDER BY id DESC LIMIT 100')->fetchAll();
$total = (int) db()->query('SELECT COUNT(*) FROM cv_views')->fetchColumn();
$people = (int) db()->query('SELECT COUNT(DISTINCT visitor) FROM cv_views')->fetchColumn();

admin_header('Resume & views', 'resume');
?>

<div class="grid-2 top">
  <div class="card">
    <div class="card-head"><h2>Current resume</h2></div>
    <?php if (file_exists($cvFile)): ?>
      <div class="file-box">
        <span class="file-ic">PDF</span>
        <div>
          <strong>Nihal_Resume.pdf</strong>
          <span class="muted"><?= round(filesize($cvFile) / 1024) ?> KB · updated <?= date('d M Y', filemtime($cvFile)) ?></span>
        </div>
        <a href="../cv/Nihal_Resume.pdf?preview=<?= filemtime($cvFile) ?>" target="_blank" class="btn btn-ghost btn-sm">Open</a>
      </div>
    <?php else: ?>
      <p class="empty">No resume uploaded yet. The resume buttons on the site won't work until you add one.</p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="upload">
      <?= csrf_field() ?>
      <label class="drop">
        <input type="file" name="cv" accept="application/pdf" required>
        <span>Choose new PDF</span>
        <small>It replaces the old one. Max 10 MB.</small>
      </label>
      <button class="btn btn-lime">Upload</button>
    </form>
  </div>

  <div class="card">
    <div class="card-head"><h2>Resume stats</h2></div>
    <div class="mini-stats">
      <div><strong><?= $total ?></strong><span>Total views</span></div>
      <div><strong><?= $people ?></strong><span>Different people</span></div>
    </div>
    <p class="muted small">Your own visits while logged in here are not counted. Opening the resume from this admin page doesn't count either.</p>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2>Who opened it (last 100)</h2></div>
  <?php if (!$views): ?>
    <p class="empty">Nobody has opened your resume yet.</p>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Date &amp; time</th><th>Device</th><th>Came from</th><th>IP</th></tr></thead>
        <tbody>
          <?php foreach ($views as $v): ?>
            <tr>
              <td><?= date('d M Y, h:i A', strtotime($v['created_at'])) ?> <span class="muted">(<?= time_ago($v['created_at']) ?>)</span></td>
              <td><?= e($v['device']) ?></td>
              <td><?= e($v['referrer']) ?></td>
              <td class="muted"><?= e($v['ip']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php admin_footer(); ?>
