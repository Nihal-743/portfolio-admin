<?php
require 'inc/boot.php';

$cats = ['shop' => 'E-commerce', 'travel' => 'Travel', 'other' => 'Services & more'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if (isset($_POST['delete'])) {
        db()->prepare('DELETE FROM projects WHERE id = ?')->execute([(int) $_POST['delete']]);
        flash('Project deleted.');
        go('projects.php');
    }

    if (isset($_POST['toggle'])) {
        db()->prepare('UPDATE projects SET visible = 1 - visible WHERE id = ?')->execute([(int) $_POST['toggle']]);
        go('projects.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');

    if ($title === '' || $url === '') {
        flash('Title and website link are required.', 'error');
        go('projects.php?' . ($id ? 'edit=' . $id : 'new=1'));
    }
    if (!preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    $category = array_key_exists($_POST['category'] ?? '', $cats) ? $_POST['category'] : 'other';

    try {
        $image = upload_image('image');
    } catch (Exception $ex) {
        flash($ex->getMessage(), 'error');
        go('projects.php?' . ($id ? 'edit=' . $id : 'new=1'));
    }

    $data = [
        $title,
        $url,
        $category,
        trim($_POST['tag'] ?? ''),
        isset($_POST['scratch']) ? 1 : 0,
        (int) ($_POST['sort'] ?? 0),
        isset($_POST['visible']) ? 1 : 0,
    ];

    if ($id) {
        $sql = 'UPDATE projects SET title = ?, url = ?, category = ?, tag = ?, scratch = ?, sort = ?, visible = ?';
        if ($image) {
            $sql .= ', image = ?';
            $data[] = $image;
        }
        $data[] = $id;
        db()->prepare($sql . ' WHERE id = ?')->execute($data);
        flash('Project saved.');
    } else {
        $data[] = $image;
        db()->prepare('INSERT INTO projects (title, url, category, tag, scratch, sort, visible, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')->execute($data);
        flash('Project added.');
    }
    go('projects.php');
}

// add / edit form
if (isset($_GET['edit']) || isset($_GET['new'])) {
    $p = ['title' => '', 'url' => '', 'category' => 'other', 'tag' => '', 'scratch' => 0, 'image' => '', 'visible' => 1];
    $p['sort'] = (int) db()->query('SELECT COALESCE(MAX(sort), 0) + 10 FROM projects')->fetchColumn();

    if (!empty($_GET['edit'])) {
        $st = db()->prepare('SELECT * FROM projects WHERE id = ?');
        $st->execute([(int) $_GET['edit']]);
        $p = $st->fetch() ?: $p;
    }

    admin_header(!empty($p['id']) ? 'Edit project' : 'Add project', 'projects');
    ?>
    <a href="projects.php" class="back">← Back to projects</a>

    <form method="post" enctype="multipart/form-data" class="card form-card">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) ($p['id'] ?? 0) ?>">

      <div class="project-form">
        <div class="fields">
          <div class="field">
            <label for="title">Project name</label>
            <input id="title" name="title" value="<?= e($p['title']) ?>" required>
          </div>
          <div class="field">
            <label for="url">Live website link</label>
            <input id="url" name="url" value="<?= e($p['url']) ?>" placeholder="https://example.com" required>
          </div>
          <div class="field half">
            <label for="category">Filter category</label>
            <select id="category" name="category">
              <?php foreach ($cats as $k => $v): ?>
                <option value="<?= $k ?>"<?= $p['category'] === $k ? ' selected' : '' ?>><?= e($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field half">
            <label for="tag">Tag on card</label>
            <input id="tag" name="tag" value="<?= e($p['tag']) ?>" placeholder="e.g. Tour & travel">
          </div>
          <div class="field half">
            <label for="sort">Order</label>
            <input id="sort" type="number" name="sort" value="<?= (int) $p['sort'] ?>">
            <small class="hint">Smaller number shows first.</small>
          </div>
          <div class="field half checks">
            <label class="check"><input type="checkbox" name="visible" value="1"<?= $p['visible'] ? ' checked' : '' ?>> Show on site</label>
          </div>
        </div>

        <div class="field">
          <label>Screenshot</label>
          <div class="shot-preview">
            <?php if ($p['image']): ?>
              <img src="../<?= e($p['image']) ?>" alt="" id="shotPreview">
            <?php else: ?>
              <img src="" alt="" id="shotPreview" hidden>
              <span class="muted" id="noShot">No screenshot yet</span>
            <?php endif; ?>
          </div>
          <label class="drop">
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-preview="shotPreview">
            <span><?= $p['image'] ? 'Replace screenshot' : 'Upload screenshot' ?></span>
            <small>Desktop homepage, 16:10 works best (e.g. 1440×900). Max 4 MB.</small>
          </label>
        </div>
      </div>

      <div class="form-actions">
        <button class="btn btn-lime">Save project</button>
        <a href="projects.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
    <?php
    admin_footer();
    exit;
}

$projects = all_rows('projects');
admin_header('Projects', 'projects');
?>

<div class="page-head">
  <p class="muted"><?= count($projects) ?> projects. Hidden ones stay here but don't show on the site.</p>
  <a href="?new=1" class="btn btn-lime">+ Add project</a>
</div>

<div class="project-grid">
  <?php foreach ($projects as $p): ?>
    <div class="card p-card<?= $p['visible'] ? '' : ' hidden-p' ?>">
      <div class="p-thumb">
        <?php if ($p['image']): ?><img src="../<?= e($p['image']) ?>" alt="" loading="lazy"><?php endif; ?>
        <?php if (!$p['visible']): ?><span class="badge-hidden">Hidden</span><?php endif; ?>
      </div>
      <div class="p-info">
        <strong><?= e($p['title']) ?></strong>
        <span class="muted"><?= e($cats[$p['category']] ?? '') ?></span>
      </div>
      <div class="list-actions">
        <a href="?edit=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
        <form method="post">
          <?= csrf_field() ?>
          <button name="toggle" value="<?= $p['id'] ?>" class="btn btn-ghost btn-sm"><?= $p['visible'] ? 'Hide' : 'Show' ?></button>
        </form>
        <form method="post" onsubmit="return confirm('Delete this project?')">
          <?= csrf_field() ?>
          <button name="delete" value="<?= $p['id'] ?>" class="btn btn-danger btn-sm">Delete</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php admin_footer(); ?>
