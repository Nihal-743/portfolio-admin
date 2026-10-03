<?php
require 'inc/boot.php';

$db = db();
$today = date('Y-m-d');

function count_of($sql, $params = [])
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return (int) $st->fetchColumn();
}

$views        = count_of('SELECT COUNT(*) FROM page_views');
$visitors     = count_of('SELECT COUNT(DISTINCT visitor) FROM page_views');
$viewsToday   = count_of('SELECT COUNT(*) FROM page_views WHERE created_at >= ?', [$today]);
$visitorsToday = count_of('SELECT COUNT(DISTINCT visitor) FROM page_views WHERE created_at >= ?', [$today]);

$cvViews      = count_of('SELECT COUNT(*) FROM cv_views');
$cvPeople     = count_of('SELECT COUNT(DISTINCT visitor) FROM cv_views');
$cvToday      = count_of('SELECT COUNT(*) FROM cv_views WHERE created_at >= ?', [$today]);

$enquiries    = count_of('SELECT COUNT(*) FROM enquiries');
$unread       = unread_count();

// last 14 days for the chart
$from = date('Y-m-d', strtotime('-13 days'));
$days = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $days[$d] = ['views' => 0, 'cv' => 0];
}

$st = $db->prepare('SELECT substr(created_at, 1, 10) AS d, COUNT(*) AS c FROM page_views WHERE created_at >= ? GROUP BY d');
$st->execute([$from]);
foreach ($st as $r) {
    if (isset($days[$r['d']])) $days[$r['d']]['views'] = (int) $r['c'];
}

$st = $db->prepare('SELECT substr(created_at, 1, 10) AS d, COUNT(*) AS c FROM cv_views WHERE created_at >= ? GROUP BY d');
$st->execute([$from]);
foreach ($st as $r) {
    if (isset($days[$r['d']])) $days[$r['d']]['cv'] = (int) $r['c'];
}

$max = 1;
foreach ($days as $d) $max = max($max, $d['views'], $d['cv']);

// where people come from + devices
$sources = $db->query('SELECT referrer, COUNT(*) AS c FROM page_views GROUP BY referrer ORDER BY c DESC LIMIT 6')->fetchAll();
$devices = $db->query('SELECT device, COUNT(*) AS c FROM page_views GROUP BY device ORDER BY c DESC')->fetchAll();

$recentCv  = $db->query('SELECT * FROM cv_views ORDER BY id DESC LIMIT 6')->fetchAll();
$recentEnq = $db->query('SELECT * FROM enquiries ORDER BY id DESC LIMIT 5')->fetchAll();

admin_header('Dashboard', 'dashboard');
?>

<p class="hello">Hi <?= e($_SESSION['admin_name']) ?>, here's how your portfolio is doing.</p>

<div class="stat-grid">
  <div class="stat-card">
    <span class="stat-label">Site visits</span>
    <strong><?= number_format($views) ?></strong>
    <span class="stat-foot"><b><?= $viewsToday ?></b> today</span>
  </div>
  <div class="stat-card">
    <span class="stat-label">Unique visitors</span>
    <strong><?= number_format($visitors) ?></strong>
    <span class="stat-foot"><b><?= $visitorsToday ?></b> today</span>
  </div>
  <div class="stat-card highlight">
    <span class="stat-label">Resume views</span>
    <strong><?= number_format($cvViews) ?></strong>
    <span class="stat-foot"><b><?= $cvPeople ?></b> different people · <b><?= $cvToday ?></b> today</span>
  </div>
  <a href="enquiries.php" class="stat-card">
    <span class="stat-label">Enquiries</span>
    <strong><?= number_format($enquiries) ?></strong>
    <span class="stat-foot"><?= $unread ? '<b class="dot-new">' . $unread . ' unread</b>' : 'all read' ?></span>
  </a>
</div>

<div class="grid-2">
  <div class="card chart-card">
    <div class="card-head">
      <h2>Last 14 days</h2>
      <div class="legend">
        <span><i class="lg-views"></i> Visits</span>
        <span><i class="lg-cv"></i> Resume views</span>
      </div>
    </div>

    <div class="chart" role="img" aria-label="Visits and resume views for the last 14 days">
      <?php foreach ($days as $date => $d): ?>
        <div class="bar-group" title="<?= date('d M', strtotime($date)) ?>: <?= $d['views'] ?> visits, <?= $d['cv'] ?> resume views">
          <div class="bars">
            <span class="bar-v" style="height: <?= round($d['views'] / $max * 100) ?>%"></span>
            <span class="bar-c" style="height: <?= round($d['cv'] / $max * 100) ?>%"></span>
          </div>
          <small><?= date('d', strtotime($date)) ?></small>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Where visitors come from</h2></div>
    <?php if (!$sources): ?>
      <p class="empty">No visits yet. Share your link!</p>
    <?php else: ?>
      <ul class="meter-list">
        <?php foreach ($sources as $s): $pct = round($s['c'] / max(1, $views) * 100); ?>
          <li>
            <div class="meter-top"><span><?= e($s['referrer']) ?></span><b><?= $s['c'] ?></b></div>
            <div class="meter"><span style="width: <?= $pct ?>%"></span></div>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="devices">
        <?php foreach ($devices as $dv): ?>
          <span class="chip"><?= e($dv['device']) ?> <b><?= round($dv['c'] / max(1, $views) * 100) ?>%</b></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="card-head">
      <h2>Recent resume views</h2>
      <a href="resume.php" class="link">See all</a>
    </div>
    <?php if (!$recentCv): ?>
      <p class="empty">Nobody has opened your resume yet.</p>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>When</th><th>Device</th><th>From</th></tr></thead>
        <tbody>
          <?php foreach ($recentCv as $v): ?>
            <tr>
              <td><?= time_ago($v['created_at']) ?></td>
              <td><?= e($v['device']) ?></td>
              <td><?= e($v['referrer']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-head">
      <h2>Latest enquiries</h2>
      <a href="enquiries.php" class="link">See all</a>
    </div>
    <?php if (!$recentEnq): ?>
      <p class="empty">No messages yet.</p>
    <?php else: ?>
      <ul class="enq-mini">
        <?php foreach ($recentEnq as $q): ?>
          <li>
            <a href="enquiries.php?view=<?= $q['id'] ?>">
              <span class="avatar"><?= e(strtoupper(mb_substr($q['name'], 0, 1))) ?></span>
              <div>
                <strong><?= e($q['name']) ?><?= $q['company'] ? ' · ' . e($q['company']) : '' ?></strong>
                <span class="muted"><?= e(mb_strimwidth($q['message'], 0, 70, '...')) ?></span>
              </div>
              <?php if (!$q['is_read']): ?><b class="new">New</b><?php endif; ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<?php admin_footer(); ?>
