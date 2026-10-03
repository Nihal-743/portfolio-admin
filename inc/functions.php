<?php
require_once __DIR__ . '/db.php';

date_default_timezone_set('Asia/Kolkata');

if (session_status() === PHP_SESSION_NONE) {
    session_name('nihal_sess');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// escape for html
function e($str)
{
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

// get one setting
function setting($key, $default = '')
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (db()->query('SELECT key, value FROM settings') as $row) {
            $all[$row['key']] = $row['value'];
        }
    }
    return isset($all[$key]) && $all[$key] !== '' ? $all[$key] : $default;
}

function save_setting($key, $value)
{
    db()->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value')
        ->execute([$key, $value]);
}

// textarea -> array of lines
function lines($text)
{
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
        $line = trim($line);
        if ($line !== '') $out[] = $line;
    }
    return $out;
}

// textarea -> paragraphs (blank line between them)
function paragraphs($text)
{
    $parts = preg_split('/(\r?\n){2,}/', trim((string) $text));
    return array_filter(array_map('trim', $parts));
}

// *word* -> <em>word</em>  (used for the lime highlighted word in headings)
function highlight($text)
{
    return preg_replace('/\*(.+?)\*/', '<em>$1</em>', e($text));
}

// css/js link with file time so the browser always gets the new version
function asset($path)
{
    $file = ROOT . '/' . $path;
    $v = file_exists($file) ? filemtime($file) : time();
    return $path . '?v=' . $v;
}

function all_rows($table, $order = 'sort, id')
{
    return db()->query("SELECT * FROM $table ORDER BY $order")->fetchAll();
}

function is_admin()
{
    return !empty($_SESSION['admin_id']);
}

// all social platforms the admin can fill in. key => [label, icon]
function social_platforms()
{
    return [
        'github'    => ['GitHub', 'i-github'],
        'linkedin'  => ['LinkedIn', 'i-linkedin'],
        'instagram' => ['Instagram', 'i-instagram'],
        'facebook'  => ['Facebook', 'i-facebook'],
        'twitter'   => ['X (Twitter)', 'i-x'],
        'youtube'   => ['YouTube', 'i-youtube'],
        'dribbble'  => ['Dribbble', 'i-dribbble'],
        'website'   => ['Website', 'i-globe'],
    ];
}

// only the links that are filled in, ready to print
function social_links($withContact = true)
{
    $out = [];
    foreach (social_platforms() as $key => $p) {
        $url = setting($key);
        if ($url === '') continue;
        if (!preg_match('#^https?://#i', $url)) $url = 'https://' . $url;
        $out[] = ['label' => $p[0], 'icon' => $p[1], 'url' => $url, 'blank' => true];
    }

    if ($withContact) {
        $wa = preg_replace('/\D/', '', setting('whatsapp'));
        if ($wa !== '') {
            $out[] = ['label' => 'WhatsApp', 'icon' => 'i-whatsapp', 'url' => 'https://wa.me/' . $wa, 'blank' => true];
        }
        if (setting('email') !== '') {
            $out[] = ['label' => 'Email', 'icon' => 'i-mail', 'url' => 'mailto:' . setting('email'), 'blank' => false];
        }
    }
    return $out;
}


// ---------- visit tracking ----------

function is_bot()
{
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '') return true;
    return (bool) preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|curl|wget|python|headless|lighthouse/', $ua);
}

function device_type()
{
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (strpos($ua, 'ipad') !== false || strpos($ua, 'tablet') !== false) return 'Tablet';
    if (strpos($ua, 'mobi') !== false || strpos($ua, 'android') !== false) return 'Mobile';
    return 'Desktop';
}

// random id kept in a cookie so we can count unique visitors
function visitor_id()
{
    if (!empty($_COOKIE['vid']) && preg_match('/^[a-f0-9]{16}$/', $_COOKIE['vid'])) {
        return $_COOKIE['vid'];
    }
    $id = bin2hex(random_bytes(8));
    setcookie('vid', $id, [
        'expires'  => time() + 60 * 60 * 24 * 365,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['vid'] = $id;
    return $id;
}

function referrer_host()
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if ($ref === '') return 'Direct';
    $host = parse_url($ref, PHP_URL_HOST);
    if (!$host) return 'Direct';
    // own site doesn't count as a source
    if ($host === ($_SERVER['HTTP_HOST'] ?? '')) return 'Direct';
    return preg_replace('/^www\./', '', $host);
}

function track_page_view()
{
    if (is_bot() || is_admin()) return;

    db()->prepare('INSERT INTO page_views (visitor, device, referrer, created_at) VALUES (?, ?, ?, ?)')
        ->execute([visitor_id(), device_type(), referrer_host(), date('Y-m-d H:i:s')]);
}

function track_cv_view()
{
    if (is_bot() || is_admin()) return;

    db()->prepare('INSERT INTO cv_views (visitor, device, referrer, ip, created_at) VALUES (?, ?, ?, ?, ?)')
        ->execute([visitor_id(), device_type(), referrer_host(), $_SERVER['REMOTE_ADDR'] ?? '', date('Y-m-d H:i:s')]);
}
