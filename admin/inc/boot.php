<?php
// loaded at the top of every admin page
require_once dirname(__DIR__, 2) . '/inc/functions.php';

if (!defined('NO_LOGIN_NEEDED') && !is_admin()) {
    header('Location: login.php');
    exit;
}


// ---------- csrf ----------

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

function check_csrf()
{
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(400);
        die('Session expired. Go back and refresh the page.');
    }
}


// ---------- flash messages ----------

function flash($msg, $type = 'ok')
{
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function show_flash()
{
    if (empty($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
}

function go($url)
{
    header('Location: ' . $url);
    exit;
}


// ---------- small helpers ----------

function time_ago($datetime)
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 86400 * 7) return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}

function unread_count()
{
    return (int) db()->query('SELECT COUNT(*) FROM enquiries WHERE is_read = 0')->fetchColumn();
}

// saves an uploaded image into img/work (or another folder), returns the path
function upload_image($field, $folder = 'img/work')
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload failed (file may be too big).');
    }
    if ($f['size'] > 4 * 1024 * 1024) {
        throw new Exception('Image must be under 4 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || !getimagesize($f['tmp_name'])) {
        throw new Exception('Only JPG, PNG or WEBP images are allowed.');
    }

    if (!is_dir(ROOT . '/' . $folder)) {
        mkdir(ROOT . '/' . $folder, 0755, true);
    }

    $name = $folder . '/' . date('ymd') . '-' . bin2hex(random_bytes(4)) . '.' . $types[$mime];
    move_uploaded_file($f['tmp_name'], ROOT . '/' . $name);
    return $name;
}


// ---------- layout ----------

function nav_icon($name)
{
    $icons = [
        'home'     => '<path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>',
        'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/>',
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'brief'    => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
        'code'     => '<path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/>',
        'steps'    => '<path d="M4 6h16M4 12h10M4 18h6"/>',
        'cap'      => '<path d="M22 10 12 5 2 10l10 5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/>',
        'gear'     => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
        'out'      => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'external' => '<path d="M7 17 17 7M8 7h9v9"/>',
    ];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . ($icons[$name] ?? '') . '</svg>';
}

function admin_header($title, $active = '')
{
    $unread = unread_count();
    $menu = [
        ['index.php', 'Dashboard', 'home', 'dashboard'],
        ['enquiries.php', 'Enquiries', 'mail', 'enquiries'],
        ['resume.php', 'Resume & views', 'file', 'resume'],
        'Content',
        ['content.php', 'Page content', 'edit', 'content'],
        ['projects.php', 'Projects', 'grid', 'projects'],
        ['experience.php', 'Experience', 'brief', 'experience'],
        ['skills.php', 'Skills', 'code', 'skills'],
        ['process.php', 'How I work', 'steps', 'process'],
        ['education.php', 'Education', 'cap', 'education'],
        'Settings',
        ['settings.php', 'General settings', 'gear', 'settings'],
        ['account.php', 'Account', 'user', 'account'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> · Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__ . '/../admin.css') ?>">
</head>
<body>

<aside class="sidebar" id="sidebar">
  <a href="index.php" class="brand"><b>nihal<i>.</i></b> <span>admin</span></a>

  <nav class="side-nav">
    <?php foreach ($menu as $item): ?>
      <?php if (is_string($item)): ?>
        <p class="side-label"><?= $item ?></p>
      <?php else: ?>
        <a href="<?= $item[0] ?>" class="<?= $active === $item[3] ? 'on' : '' ?>">
          <?= nav_icon($item[2]) ?> <?= $item[1] ?>
          <?php if ($item[3] === 'enquiries' && $unread): ?><b class="count"><?= $unread ?></b><?php endif; ?>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </nav>

  <a href="logout.php" class="side-logout"><?= nav_icon('out') ?> Log out</a>
</aside>

<div class="overlay" id="overlay"></div>

<div class="main">
  <header class="topbar">
    <button class="menu-btn" id="menuBtn" aria-label="Open menu">
      <span></span><span></span><span></span>
    </button>
    <h1><?= e($title) ?></h1>
    <a href="../" target="_blank" class="btn btn-ghost btn-sm">View site <?= nav_icon('external') ?></a>
  </header>

  <div class="content">
    <?= show_flash() ?>
    <?php
}

function admin_footer()
{
    ?>
  </div>
</div>

<script src="admin.js?v=<?= filemtime(__DIR__ . '/../admin.js') ?>"></script>
</body>
</html>
    <?php
}


// ---------- simple list + form page (used by experience, skills, process, education) ----------
// each page just says which fields it has, this does the rest

function render_field($f, $value)
{
    $name = $f['name'];
    $req  = !empty($f['required']) ? ' required' : '';
    $html = '<div class="field' . (!empty($f['half']) ? ' half' : '') . '">';

    if ($f['type'] === 'check') {
        $html .= '<label class="check"><input type="checkbox" name="' . $name . '" value="1"' . ($value ? ' checked' : '') . '> ' . e($f['label']) . '</label>';
    } else {
        $html .= '<label for="f-' . $name . '">' . e($f['label']) . '</label>';
        if ($f['type'] === 'textarea') {
            $rows = $f['rows'] ?? 5;
            $html .= '<textarea id="f-' . $name . '" name="' . $name . '" rows="' . $rows . '"' . $req . '>' . e($value) . '</textarea>';
        } else {
            $type = $f['type'] === 'number' ? 'number' : 'text';
            $html .= '<input id="f-' . $name . '" type="' . $type . '" name="' . $name . '" value="' . e($value) . '"' . $req . '>';
        }
    }

    if (!empty($f['hint'])) {
        $html .= '<small class="hint">' . e($f['hint']) . '</small>';
    }
    return $html . '</div>';
}

function crud_page($cfg)
{
    $table  = $cfg['table'];
    $fields = $cfg['fields'];
    $self   = basename($_SERVER['PHP_SELF']);

    // save / delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_csrf();

        if (isset($_POST['delete'])) {
            db()->prepare("DELETE FROM $table WHERE id = ?")->execute([(int) $_POST['delete']]);
            flash('Deleted.');
            go($self);
        }

        $cols = [];
        $vals = [];
        foreach ($fields as $f) {
            $cols[] = $f['name'];
            if ($f['type'] === 'check') {
                $vals[] = isset($_POST[$f['name']]) ? 1 : 0;
            } elseif ($f['type'] === 'number') {
                $vals[] = (int) ($_POST[$f['name']] ?? 0);
            } else {
                $vals[] = trim($_POST[$f['name']] ?? '');
            }
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $set = implode(' = ?, ', $cols) . ' = ?';
            $vals[] = $id;
            db()->prepare("UPDATE $table SET $set WHERE id = ?")->execute($vals);
            flash('Saved.');
        } else {
            $place = implode(', ', array_fill(0, count($cols), '?'));
            db()->prepare("INSERT INTO $table (" . implode(', ', $cols) . ") VALUES ($place)")->execute($vals);
            flash('Added.');
        }
        go($self);
    }

    admin_header($cfg['title'], $cfg['active']);

    // form (new or edit)
    if (isset($_GET['edit']) || isset($_GET['new'])) {
        $row = [];
        if (!empty($_GET['edit'])) {
            $st = db()->prepare("SELECT * FROM $table WHERE id = ?");
            $st->execute([(int) $_GET['edit']]);
            $row = $st->fetch() ?: [];
        } else {
            // put new items at the end
            $row['sort'] = (int) db()->query("SELECT COALESCE(MAX(sort), 0) + 10 FROM $table")->fetchColumn();
        }
        ?>
        <a href="<?= $self ?>" class="back">← Back to list</a>
        <form method="post" class="card form-card">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int) ($row['id'] ?? 0) ?>">
          <h2><?= !empty($row['id']) ? 'Edit ' . e($cfg['singular']) : 'Add ' . e($cfg['singular']) ?></h2>
          <div class="fields">
            <?php foreach ($fields as $f) echo render_field($f, $row[$f['name']] ?? ''); ?>
          </div>
          <div class="form-actions">
            <button class="btn btn-lime">Save</button>
            <a href="<?= $self ?>" class="btn btn-ghost">Cancel</a>
          </div>
        </form>
        <?php
        admin_footer();
        return;
    }

    // list
    $rows = all_rows($table);
    ?>
    <div class="page-head">
      <p class="muted"><?= e($cfg['intro'] ?? '') ?></p>
      <a href="?new=1" class="btn btn-lime">+ Add <?= e($cfg['singular']) ?></a>
    </div>

    <div class="card list">
      <?php if (!$rows): ?>
        <p class="empty">Nothing here yet.</p>
      <?php endif; ?>
      <?php foreach ($rows as $r): ?>
        <div class="list-row">
          <span class="order"><?= (int) $r['sort'] ?></span>
          <div class="list-main">
            <strong><?= e($r[$cfg['main']]) ?></strong>
            <?php if (!empty($cfg['sub'])): ?><span class="muted"><?= e($r[$cfg['sub']]) ?></span><?php endif; ?>
          </div>
          <div class="list-actions">
            <a href="?edit=<?= $r['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
            <form method="post" onsubmit="return confirm('Delete this?')">
              <?= csrf_field() ?>
              <button name="delete" value="<?= $r['id'] ?>" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php
    admin_footer();
}
