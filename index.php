<?php
require 'inc/functions.php';

track_page_view();

$projects  = db()->query('SELECT * FROM projects WHERE visible = 1 ORDER BY sort, id')->fetchAll();
$jobs      = all_rows('jobs');
$skills    = all_rows('skill_groups');
$steps     = all_rows('steps');
$education = all_rows('education');

$typedWords = lines(setting('typed_words'));
$firstWord  = $typedWords[0] ?? '';
$strip      = lines(setting('strip_items'));

$email    = setting('email');
$phone    = setting('phone');
$whatsapp = preg_replace('/\D/', '', setting('whatsapp'));
$location = setting('location');
$socials  = social_links();

// logo: name + role ("Front-End Developer · Web Designer", part after · hides on small screens)
$siteName = setting('site_name', 'Nihal');
$roleParts = array_map('trim', explode('·', setting('site_role', 'Front-End Developer · Web Designer'), 2));

$cvLink = 'cv.php';

// "React.js (basic)" -> name + basic flag, "HTML5 *" -> highlighted
function skill_parts($line)
{
    $basic = (bool) preg_match('/\(basic\)\s*$/i', $line);
    $hot   = substr($line, -1) === '*';
    $name  = trim(preg_replace(['/\(basic\)\s*$/i', '/\*$/'], '', $line));
    return [$name, $basic, $hot];
}

$catNames = ['shop' => 'E-commerce', 'travel' => 'Travel', 'other' => 'Services & more'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e(setting('meta_title')) ?></title>
  <meta name="description" content="<?= e(setting('meta_description')) ?>">
  <meta name="theme-color" content="#0b0b0c">

  <!-- for whatsapp / linkedin previews -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e(setting('meta_title')) ?>">
  <meta property="og:description" content="<?= e(setting('meta_description')) ?>">
  <meta property="og:image" content="<?= e(setting('photo')) ?>">

  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23c8f031'/%3E%3Ctext x='16' y='23' font-family='Arial' font-weight='800' font-size='19' fill='%230b0b0c' text-anchor='middle'%3EN%3C/text%3E%3C/svg%3E">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Manrope:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= asset('style.css') ?>">
</head>
<body>

<a class="skip" href="#main">Skip to content</a>

<!-- svg icons -->
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-github" viewBox="0 0 24 24"><path fill="currentColor" d="M12 .3a12 12 0 0 0-3.8 23.4c.6.1.8-.3.8-.6v-2c-3.3.7-4-1.6-4-1.6-.6-1.4-1.4-1.8-1.4-1.8-1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1.1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.8-1.6-2.7-.3-5.5-1.3-5.5-5.9 0-1.3.5-2.4 1.2-3.2-.1-.3-.5-1.5.1-3.2 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17.3 4.7 18.3 5 18.3 5c.7 1.7.2 2.9.1 3.2.8.8 1.2 1.9 1.2 3.2 0 4.6-2.8 5.6-5.5 5.9.4.4.8 1.1.8 2.2v3.3c0 .3.2.7.8.6A12 12 0 0 0 12 .3"/></symbol>
  <symbol id="i-linkedin" viewBox="0 0 24 24"><path fill="currentColor" d="M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.2 1.5-2.2 2.9v5.7H9.4V9h3.4v1.6c.5-.9 1.6-1.9 3.4-1.9 3.6 0 4.3 2.4 4.3 5.5v6.3zM5.3 7.4a2.1 2.1 0 1 1 0-4.1 2.1 2.1 0 0 1 0 4.1zm1.8 13.1H3.6V9h3.5v11.5zM22.2 0H1.8C.8 0 0 .8 0 1.7v20.6c0 .9.8 1.7 1.8 1.7h20.4c1 0 1.8-.8 1.8-1.7V1.7C24 .8 23.2 0 22.2 0z"/></symbol>
  <symbol id="i-whatsapp" viewBox="0 0 24 24"><path fill="currentColor" d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.08-.3-.15-1.26-.46-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.14.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.07c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.22 1.36.19 1.87.12.57-.09 1.76-.72 2-1.41.25-.7.25-1.29.18-1.41-.08-.13-.28-.2-.57-.35m-5.42 7.4h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.89-9.88 2.64 0 5.12 1.03 6.99 2.9a9.83 9.83 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.88 9.88m8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.69 1.45h.01c6.55 0 11.89-5.34 11.89-11.89a11.82 11.82 0 0 0-3.48-8.41z"/></symbol>
  <symbol id="i-mail" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></g></symbol>
  <symbol id="i-phone" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></symbol>
  <symbol id="i-pin" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></g></symbol>
  <symbol id="i-arrow" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M8 7h9v9"/></symbol>
  <symbol id="i-instagram" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.4A4 4 0 1 1 12.6 8 4 4 0 0 1 16 11.4zM17.5 6.5h.01"/></g></symbol>
  <symbol id="i-facebook" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><path fill="currentColor" d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93zM17.61 20.64h2.04L6.49 3.24H4.3z"/></symbol>
  <symbol id="i-youtube" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.5 6.4a2.8 2.8 0 0 0-1.9-2C18.9 4 12 4 12 4s-6.9 0-8.6.5a2.8 2.8 0 0 0-1.9 2A29 29 0 0 0 1 11.8a29 29 0 0 0 .5 5.3 2.8 2.8 0 0 0 1.9 1.9c1.7.5 8.6.5 8.6.5s6.9 0 8.6-.5a2.8 2.8 0 0 0 1.9-2 29 29 0 0 0 .5-5.2 29 29 0 0 0-.5-5.4z"/><path d="m9.8 15 5.7-3.2-5.7-3.3z"/></g></symbol>
  <symbol id="i-dribbble" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8.6 2.8c4.3 6 6 9.4 8 17.7M19.1 5.1c-3.7 4.4-8.9 5.7-16.9 5.9M21.8 12.8c-3.5-.9-6.7-.8-9 0-2.6.9-5 2.9-7.4 6.3"/></g></symbol>
  <symbol id="i-globe" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></g></symbol>
</svg>

<!-- header -->
<header class="header" id="header">
  <div class="container header-in">
    <a href="#top" class="logo" aria-label="<?= e($siteName) ?>">
      <span class="logo-name"><?= e(strtolower($siteName)) ?><i>.</i></span>
      <?php if ($roleParts[0] !== ''): ?><span class="logo-role"><?= e($roleParts[0]) ?><?php if (!empty($roleParts[1])): ?><b> · <?= e($roleParts[1]) ?></b><?php endif; ?></span><?php endif; ?>
    </a>

    <nav class="nav" id="nav" aria-label="Main">
      <a href="#about">About</a>
      <a href="#work">Work</a>
      <a href="#experience">Experience</a>
      <a href="#skills">Skills</a>
      <a href="#contact">Contact</a>
      <a href="<?= $cvLink ?>" class="btn btn-lime nav-cv" target="_blank" rel="noopener">View Resume</a>
    </nav>

    <a href="<?= $cvLink ?>" class="btn btn-lime btn-sm header-cta" target="_blank" rel="noopener">
      Resume <svg class="ic"><use href="#i-arrow"/></svg>
    </a>

    <button class="burger" id="burger" aria-label="Open menu" aria-expanded="false" aria-controls="nav">
      <span></span><span></span>
    </button>
  </div>
</header>

<main id="main">

  <!-- hero -->
  <section class="hero" id="top">
    <div class="container hero-grid">
      <div class="hero-text">
        <p class="badge"><span class="pulse"></span> <?= e(setting('hero_badge')) ?></p>

        <p class="hello"><?= e(setting('hero_hello')) ?></p>
        <h1><span class="l1"><?= e(setting('hero_title')) ?></span><br><span class="typed" id="typed" data-words="<?= e(json_encode($typedWords)) ?>"><?= e($firstWord) ?></span></h1>

        <p class="lead"><?= e(setting('hero_lead')) ?></p>

        <div class="hero-btns">
          <a href="<?= $cvLink ?>" class="btn btn-lime" target="_blank" rel="noopener">
            View Resume <svg class="ic"><use href="#i-arrow"/></svg>
          </a>
          <a href="#work" class="btn btn-line">See my work</a>
        </div>

        <?php if ($socials): ?>
        <div class="socials">
          <?php foreach ($socials as $s): ?>
            <a href="<?= e($s['url']) ?>"<?= $s['blank'] ? ' target="_blank" rel="noopener"' : '' ?> aria-label="<?= e($s['label']) ?>" title="<?= e($s['label']) ?>"><svg><use href="#<?= $s['icon'] ?>"/></svg></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="hero-photo">
        <div class="photo-frame">
          <img src="<?= e(setting('photo', 'img/nihal.jpg')) ?>" alt="Nihal" width="720" height="960">
        </div>
        <div class="float f1"><strong><?= e(setting('stat_sites')) ?>+</strong> live websites</div>
        <div class="float f2"><strong><?= e(setting('stat_years')) ?>+</strong> years experience</div>
        <div class="float f3"><code>&lt;/&gt;</code> <?= e(setting('float_stack')) ?></div>
      </div>
    </div>
  </section>

  <!-- moving strip (list is printed twice so the loop looks seamless) -->
  <div class="strip" aria-hidden="true">
    <div class="strip-track">
      <?php for ($x = 0; $x < 2; $x++): ?>
        <?php foreach ($strip as $item): ?><span><?= e($item) ?></span><?php endforeach; ?>
      <?php endfor; ?>
    </div>
  </div>

  <!-- about -->
  <section class="section" id="about">
    <div class="container">
      <div class="about-grid">
        <div class="reveal">
          <p class="eyebrow">About me</p>
          <h2><?= highlight(setting('about_title')) ?></h2>
          <?php foreach (paragraphs(setting('about_text')) as $p): ?>
            <p><?= e($p) ?></p>
          <?php endforeach; ?>
        </div>

        <div class="stats">
          <div class="stat reveal"><span class="num" data-count="<?= (int) setting('stat_sites') ?>">0</span><span class="plus">+</span><p>Websites live</p></div>
          <div class="stat reveal"><span class="num"><?= e(setting('stat_years')) ?></span><span class="plus">+</span><p>Years of experience</p></div>
          <div class="stat reveal"><span class="num"><?= e(setting('stat3_num')) ?></span><p><?= e(setting('stat3_text')) ?></p></div>
          <div class="stat reveal"><span class="num"><?= e(setting('stat4_num')) ?></span><p><?= e(setting('stat4_text')) ?></p></div>
        </div>
      </div>

      <!-- quick info for recruiters -->
      <dl class="facts reveal">
        <div><dt>Looking for</dt><dd><?= e(setting('fact_looking')) ?></dd></div>
        <div><dt>Job type</dt><dd><?= e(setting('fact_type')) ?></dd></div>
        <div><dt>Based in</dt><dd><?= e(setting('fact_based')) ?></dd></div>
        <div><dt>Preferred location</dt><dd><?= e(setting('fact_location')) ?></dd></div>
      </dl>
    </div>
  </section>

  <!-- projects -->
  <section class="section" id="work">
    <div class="container">
      <div class="sec-head reveal">
        <div>
          <p class="eyebrow">Selected work</p>
          <h2>Live sites I've built</h2>
          <p class="sec-sub"><?= e(setting('work_sub')) ?></p>
        </div>
        <div class="filters" role="group" aria-label="Filter projects">
          <button class="on" data-filter="all">All</button>
          <?php foreach ($catNames as $key => $label): ?>
            <button data-filter="<?= $key ?>"><?= e($label) ?></button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="projects" id="projects">
        <?php foreach ($projects as $p):
          $domain = preg_replace('/^www\./', '', parse_url($p['url'], PHP_URL_HOST) ?: $p['url']);
        ?>
        <article class="project reveal" data-cat="<?= e($p['category']) ?>">
          <a href="<?= e($p['url']) ?>" target="_blank" rel="noopener">
            <div class="shot">
              <span class="bar"><i></i><i></i><i></i><?= e($domain) ?></span>
              <?php if ($p['image']): ?>
                <img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?> homepage" loading="lazy" width="960" height="600">
              <?php endif; ?>
            </div>
            <div class="p-body">
              <span class="tag"><?= e($p['tag']) ?></span>
              <h3><?= e($p['title']) ?></h3>
              <span class="go" aria-hidden="true"><svg class="ic"><use href="#i-arrow"/></svg></span>
            </div>
          </a>
        </article>
        <?php endforeach; ?>
      </div>

      <?php if (setting('work_more')): ?>
        <p class="more reveal"><?= e(setting('work_more')) ?></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- experience -->
  <section class="section" id="experience">
    <div class="container split">
      <div class="split-head reveal">
        <p class="eyebrow">Experience</p>
        <h2>Where I've worked</h2>
        <p class="sec-sub"><?= e(setting('exp_sub')) ?></p>
      </div>

      <ol class="timeline">
        <?php foreach ($jobs as $job): ?>
        <li class="reveal<?= $job['is_current'] ? ' current' : '' ?>">
          <span class="date"><?= e($job['period']) ?></span>
          <h3><?= e($job['title']) ?></h3>
          <p class="company"><?= e($job['company']) ?></p>
          <?php $points = lines($job['points']); if ($points): ?>
          <ul>
            <?php foreach ($points as $pt): ?><li><?= e($pt) ?></li><?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>

  <!-- skills -->
  <section class="section" id="skills">
    <div class="container split">
      <div class="split-head reveal">
        <p class="eyebrow">Skills</p>
        <h2>My toolkit</h2>
        <p class="sec-sub"><?= str_replace(e('basic'), '<em class="lvl">basic</em>', e(setting('skills_sub'))) ?></p>
      </div>

      <div class="skill-rows">
        <?php foreach ($skills as $group): ?>
        <div class="skill-row reveal">
          <h3><?= e($group['name']) ?><?php if ($group['label']): ?> <span class="main-tag"><?= e($group['label']) ?></span><?php endif; ?></h3>
          <ul class="chips">
            <?php foreach (lines($group['items']) as $line):
              list($name, $basic, $hot) = skill_parts($line); ?>
              <li<?= $hot ? ' class="hot"' : '' ?>><?= e($name) ?><?php if ($basic): ?> <em class="lvl">basic</em><?php endif; ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- how i work -->
  <?php if ($steps): ?>
  <section class="section" id="process">
    <div class="container">
      <div class="sec-head reveal">
        <div>
          <p class="eyebrow">How I work</p>
          <h2>From brief to live site</h2>
          <p class="sec-sub">Roughly the steps every project goes through on my desk.</p>
        </div>
      </div>

      <ol class="steps">
        <?php foreach ($steps as $i => $step): ?>
        <li class="step reveal">
          <span class="step-no"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
          <h3><?= e($step['title']) ?></h3>
          <p><?= e($step['text']) ?></p>
        </li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>
  <?php endif; ?>

  <!-- education -->
  <section class="section" id="education">
    <div class="container">
      <div class="reveal">
        <p class="eyebrow">Education</p>
        <h2>Still learning</h2>
      </div>

      <div class="edu-grid">
        <?php foreach ($education as $ed): ?>
        <div class="edu reveal">
          <span class="date"><?= e($ed['period']) ?></span>
          <h3><?= e($ed['title']) ?></h3>
          <p><?= e($ed['place']) ?><?php if ($ed['pill']): ?> <span class="pill"><?= e($ed['pill']) ?></span><?php endif; ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- contact -->
  <section class="section contact" id="contact">
    <div class="container contact-grid">
      <div class="reveal">
        <p class="eyebrow">Contact</p>
        <h2><?= highlight(setting('contact_title')) ?></h2>
        <p class="contact-lead"><?= e(setting('contact_lead')) ?></p>

        <?php if ($email || $phone || $whatsapp || $location): ?>
        <ul class="c-list">
          <?php if ($email): ?>
          <li>
            <span class="c-ic"><svg><use href="#i-mail"/></svg></span>
            <div><small>Email</small><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></div>
            <button type="button" class="copy" data-copy="<?= e($email) ?>">Copy</button>
          </li>
          <?php endif; ?>
          <?php if ($phone): ?>
          <li>
            <span class="c-ic"><svg><use href="#i-phone"/></svg></span>
            <div><small>Phone</small><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a></div>
          </li>
          <?php endif; ?>
          <?php if ($whatsapp): ?>
          <li>
            <span class="c-ic"><svg><use href="#i-whatsapp"/></svg></span>
            <div><small>WhatsApp</small><a href="https://wa.me/<?= $whatsapp ?>" target="_blank" rel="noopener">Chat on WhatsApp</a></div>
          </li>
          <?php endif; ?>
          <?php if ($location): ?>
          <li>
            <span class="c-ic"><svg><use href="#i-pin"/></svg></span>
            <div><small>Location</small><span><?= e($location) ?></span></div>
          </li>
          <?php endif; ?>
        </ul>
        <?php endif; ?>

        <a href="<?= $cvLink ?>" class="btn btn-line cv-link" target="_blank" rel="noopener">
          View my resume (PDF) <svg class="ic"><use href="#i-arrow"/></svg>
        </a>
      </div>

      <form class="form reveal" id="form" action="enquiry.php" method="post" novalidate data-wa="<?= $whatsapp ?>">
        <div class="field">
          <label for="f-name">Your name</label>
          <input id="f-name" name="name" type="text" autocomplete="name" required placeholder="Rahul Sharma">
          <span class="err"></span>
        </div>
        <div class="field">
          <label for="f-contact">Email or phone</label>
          <input id="f-contact" name="contact" type="text" autocomplete="email" required placeholder="So I can get back to you">
          <span class="err"></span>
        </div>
        <div class="field">
          <label for="f-company">Company <span class="opt">(optional)</span></label>
          <input id="f-company" name="company" type="text" autocomplete="organization" placeholder="Where you're hiring for">
        </div>
        <div class="field">
          <label for="f-msg">Message</label>
          <textarea id="f-msg" name="message" rows="4" required placeholder="Tell me about the role..."></textarea>
          <span class="err"></span>
        </div>

        <!-- spam trap, hidden from people -->
        <div class="hp" aria-hidden="true">
          <label for="f-website">Website</label>
          <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>

        <button type="submit" class="btn btn-lime btn-block" id="sendBtn">Send message</button>
        <p class="form-note" id="formNote"><?php if ($whatsapp): ?>Or message me directly on <a href="https://wa.me/<?= $whatsapp ?>" target="_blank" rel="noopener">WhatsApp</a>.<?php else: ?>I'll reply on the email or phone you give.<?php endif; ?></p>
      </form>
    </div>
  </section>

</main>

<footer class="footer">
  <div class="container footer-in">
    <a href="#top" class="logo" aria-label="<?= e($siteName) ?>">
      <span class="logo-name"><?= e(strtolower($siteName)) ?><i>.</i></span>
      <?php if ($roleParts[0] !== ''): ?><span class="logo-role"><?= e($roleParts[0]) ?><?php if (!empty($roleParts[1])): ?><b> · <?= e($roleParts[1]) ?></b><?php endif; ?></span><?php endif; ?>
    </a>
    <p>Designed &amp; coded by <?= e($siteName) ?>. &copy; <span id="year"><?= date('Y') ?></span></p>
    <?php if ($socials): ?>
    <div class="socials socials-sm">
      <?php foreach ($socials as $s): ?>
        <a href="<?= e($s['url']) ?>"<?= $s['blank'] ? ' target="_blank" rel="noopener"' : '' ?> aria-label="<?= e($s['label']) ?>" title="<?= e($s['label']) ?>"><svg><use href="#<?= $s['icon'] ?>"/></svg></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</footer>

<a href="#top" class="to-top" id="toTop" aria-label="Back to top">↑</a>

<script src="<?= asset('script.js') ?>"></script>
</body>
</html>
