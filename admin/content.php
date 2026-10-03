<?php
require 'inc/boot.php';

// every text on the page that isn't a list item lives here
$groups = [
    'Hero (top of the page)' => [
        ['hero_badge', 'Green badge text', 'text'],
        ['hero_hello', 'Small intro line', 'text'],
        ['hero_title', 'Big heading', 'text'],
        ['typed_words', 'Typing words (one per line)', 'textarea', 'These type themselves after the heading, one by one.'],
        ['hero_lead', 'Intro paragraph', 'textarea'],
        ['stat_sites', 'Websites count', 'text', 'Number only, e.g. 50'],
        ['stat_years', 'Years of experience', 'text', 'e.g. 1.5'],
        ['float_stack', 'Small card under photo', 'text'],
        ['strip_items', 'Moving green strip (one per line)', 'textarea'],
    ],
    'About' => [
        ['about_title', 'Heading', 'text', 'Wrap a word in *stars* to make it green.'],
        ['about_text', 'Text', 'textarea', 'Leave an empty line between paragraphs.'],
        ['stat3_num', 'Box 3 big text', 'text'],
        ['stat3_text', 'Box 3 small text', 'text'],
        ['stat4_num', 'Box 4 big text', 'text'],
        ['stat4_text', 'Box 4 small text', 'text'],
    ],
    'Recruiter info row' => [
        ['fact_looking', 'Looking for', 'text'],
        ['fact_type', 'Job type', 'text'],
        ['fact_based', 'Based in', 'text'],
        ['fact_location', 'Preferred location', 'text'],
    ],
    'Section sub-texts' => [
        ['work_sub', 'Projects - line under heading', 'text'],
        ['work_more', 'Projects - line under cards', 'text'],
        ['exp_sub', 'Experience - line under heading', 'textarea'],
        ['skills_sub', 'Skills - line under heading', 'textarea', 'The word "basic" turns into the small grey tag.'],
    ],
    'Contact section' => [
        ['contact_title', 'Contact heading', 'text', 'Wrap words in *stars* to make them green.'],
        ['contact_lead', 'Contact text', 'textarea', 'Email, phone and social links are in General settings.'],
    ],
    'SEO (Google)' => [
        ['meta_title', 'Page title', 'text', 'Shows in the browser tab and Google results.'],
        ['meta_description', 'Description', 'textarea', 'Around 150 characters works best.'],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    foreach ($groups as $fields) {
        foreach ($fields as $f) {
            if (isset($_POST[$f[0]])) {
                save_setting($f[0], trim($_POST[$f[0]]));
            }
        }
    }

    try {
        $photo = upload_image('photo', 'img');
        if ($photo) save_setting('photo', $photo);
        flash('Content saved. Changes are live on the site.');
    } catch (Exception $ex) {
        flash('Text saved, but photo: ' . $ex->getMessage(), 'error');
    }

    go('content.php');
}

admin_header('Page content', 'content');
?>

<form method="post" enctype="multipart/form-data" class="content-form">
  <?= csrf_field() ?>

  <div class="tabs" role="tablist">
    <?php $i = 0; foreach ($groups as $name => $fields): ?>
      <button type="button" class="tab<?= $i === 0 ? ' on' : '' ?>" data-tab="t<?= $i ?>"><?= e($name) ?></button>
    <?php $i++; endforeach; ?>
    <button type="button" class="tab" data-tab="tphoto">Photo</button>
  </div>

  <?php $i = 0; foreach ($groups as $name => $fields): ?>
    <div class="card tab-panel<?= $i === 0 ? ' on' : '' ?>" id="t<?= $i ?>">
      <h2><?= e($name) ?></h2>
      <div class="fields">
        <?php foreach ($fields as $f): ?>
          <div class="field">
            <label for="s-<?= $f[0] ?>"><?= e($f[1]) ?></label>
            <?php if ($f[2] === 'textarea'): ?>
              <textarea id="s-<?= $f[0] ?>" name="<?= $f[0] ?>" rows="<?= substr_count(setting($f[0]), "\n") > 3 ? 8 : 4 ?>"><?= e(setting($f[0])) ?></textarea>
            <?php else: ?>
              <input id="s-<?= $f[0] ?>" type="text" name="<?= $f[0] ?>" value="<?= e(setting($f[0])) ?>">
            <?php endif; ?>
            <?php if (!empty($f[3])): ?><small class="hint"><?= e($f[3]) ?></small><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php $i++; endforeach; ?>

  <div class="card tab-panel" id="tphoto">
    <h2>Profile photo</h2>
    <div class="photo-row">
      <img src="../<?= e(setting('photo', 'img/nihal.jpg')) ?>" alt="" class="photo-preview" id="photoPreview">
      <label class="drop">
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" data-preview="photoPreview">
        <span>Choose new photo</span>
        <small>Portrait (4:5) looks best. JPG, PNG or WEBP, max 4 MB.</small>
      </label>
    </div>
  </div>

  <div class="save-bar">
    <span class="muted">Changes go live as soon as you save.</span>
    <button class="btn btn-lime">Save changes</button>
  </div>
</form>

<?php admin_footer(); ?>
