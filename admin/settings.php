<?php
require 'inc/boot.php';

$basic = [
    ['site_name', 'Your name', 'Shows in the logo and footer.', 'Nihal'],
    ['site_role', 'Role under logo', 'Use · between two roles, e.g. Front-End Developer · Web Designer. The second part hides on small screens.', 'Front-End Developer · Web Designer'],
];

$contact = [
    ['email', 'Email', 'Shown in contact section + email icon.', 'you@gmail.com'],
    ['phone', 'Phone number', 'Write it how you want it shown, e.g. +91 88821 11743', '+91 98765 43210'],
    ['whatsapp', 'WhatsApp number', 'Country code + number, digits only. e.g. 918882111743', '919876543210'],
    ['location', 'Location', 'City / area.', 'New Delhi'],
];

$platforms = social_platforms();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $bad = [];

    foreach (array_merge($basic, $contact) as $f) {
        $val = trim($_POST[$f[0]] ?? '');
        if ($f[0] === 'whatsapp') $val = preg_replace('/\D/', '', $val);
        if ($f[0] === 'email' && $val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            $bad[] = 'Email';
            continue;
        }
        save_setting($f[0], $val);
    }

    foreach ($platforms as $key => $p) {
        $url = trim($_POST[$key] ?? '');
        if ($url !== '' && !preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $bad[] = $p[0];
            continue;
        }
        save_setting($key, $url);
    }

    if ($bad) {
        flash('Saved, but these look wrong and were skipped: ' . implode(', ', $bad), 'error');
    } else {
        flash('Settings saved. Empty fields are hidden on the site.');
    }
    go('settings.php');
}

admin_header('General settings', 'settings');
?>

<form method="post" class="settings-form">
  <?= csrf_field() ?>

  <div class="card form-card">
    <h2>Basic info</h2>
    <div class="fields">
      <?php foreach ($basic as $f): ?>
        <div class="field">
          <label for="s-<?= $f[0] ?>"><?= e($f[1]) ?></label>
          <input id="s-<?= $f[0] ?>" type="text" name="<?= $f[0] ?>" value="<?= e(setting($f[0])) ?>" placeholder="<?= e($f[3]) ?>">
          <small class="hint"><?= e($f[2]) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card form-card">
    <h2>Contact details</h2>
    <p class="muted small card-note">Leave a field empty and it won't show anywhere on the site.</p>
    <div class="fields">
      <?php foreach ($contact as $f): ?>
        <div class="field half">
          <label for="s-<?= $f[0] ?>"><?= e($f[1]) ?></label>
          <input id="s-<?= $f[0] ?>" type="text" name="<?= $f[0] ?>" value="<?= e(setting($f[0])) ?>" placeholder="<?= e($f[3]) ?>">
          <small class="hint"><?= e($f[2]) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card form-card">
    <h2>Social media</h2>
    <p class="muted small card-note">Paste the full profile link. Only the ones you fill show up as icons in the hero and footer.</p>
    <div class="social-fields">
      <?php foreach ($platforms as $key => $p): $val = setting($key); ?>
        <div class="social-field<?= $val ? ' filled' : '' ?>">
          <span class="s-ic"><svg viewBox="0 0 24 24"><use href="#<?= $p[1] ?>"/></svg></span>
          <div class="s-input">
            <label for="s-<?= $key ?>"><?= e($p[0]) ?> <em><?= $val ? 'showing' : 'hidden' ?></em></label>
            <input id="s-<?= $key ?>" type="text" name="<?= $key ?>" value="<?= e($val) ?>" placeholder="https://">
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="save-bar">
    <span class="muted">Empty fields are hidden on the site.</span>
    <button class="btn btn-lime">Save settings</button>
  </div>
</form>

<!-- same icons as the public site -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-github" viewBox="0 0 24 24"><path fill="currentColor" d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.1-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17.3 4.7 18.3 5 18.3 5c.7 1.7.2 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3"/></symbol>
  <symbol id="i-linkedin" viewBox="0 0 24 24"><path fill="currentColor" d="M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.2 1.5-2.2 2.9v5.7H9.4V9h3.4v1.6c.5-.9 1.6-1.9 3.4-1.9 3.6 0 4.3 2.4 4.3 5.5v6.3zM5.3 7.4a2.1 2.1 0 1 1 0-4.1 2.1 2.1 0 0 1 0 4.1zm1.8 13.1H3.6V9h3.5v11.5zM22.2 0H1.8C.8 0 0 .8 0 1.7v20.6c0 .9.8 1.7 1.8 1.7h20.4c1 0 1.8-.8 1.8-1.7V1.7C24 .8 23.2 0 22.2 0z"/></symbol>
  <symbol id="i-instagram" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.4A4 4 0 1 1 12.6 8 4 4 0 0 1 16 11.4zM17.5 6.5h.01"/></g></symbol>
  <symbol id="i-facebook" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><path fill="currentColor" d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93zM17.61 20.64h2.04L6.49 3.24H4.3z"/></symbol>
  <symbol id="i-youtube" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.5 6.4a2.8 2.8 0 0 0-1.9-2C18.9 4 12 4 12 4s-6.9 0-8.6.5a2.8 2.8 0 0 0-1.9 2A29 29 0 0 0 1 11.8a29 29 0 0 0 .5 5.3 2.8 2.8 0 0 0 1.9 1.9c1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5a2.8 2.8 0 0 0 1.9-2 29 29 0 0 0 .5-5.2 29 29 0 0 0-.5-5.4z"/><path d="m9.8 15 5.7-3.2-5.7-3.3z"/></g></symbol>
  <symbol id="i-dribbble" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8.6 2.8c4.3 6 6 9.4 8 17.7M19.1 5.1c-3.7 4.4-8.9 5.7-16.9 5.9M21.8 12.8c-3.5-.9-6.7-.8-9 0-2.6.9-5 2.9-7.4 6.3"/></g></symbol>
  <symbol id="i-globe" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></g></symbol>
</svg>

<?php admin_footer(); ?>
