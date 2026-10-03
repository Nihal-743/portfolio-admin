<?php
// contact form posts here (fetch from script.js), saves it for the admin panel
require 'inc/functions.php';

header('Content-Type: application/json');

function reply($ok, $msg)
{
    echo json_encode(['ok' => $ok, 'message' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    reply(false, 'Not allowed.');
}

// hidden field, real people leave it empty
if (!empty($_POST['website'])) {
    reply(true, 'Thanks!');
}

// one message every 30 sec per visitor is enough
if (!empty($_SESSION['last_enquiry']) && time() - $_SESSION['last_enquiry'] < 30) {
    reply(false, 'Please wait a few seconds before sending again.');
}

$name    = trim($_POST['name'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$company = trim($_POST['company'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $contact === '' || mb_strlen($message) < 10) {
    reply(false, 'Please fill in your name, email/phone and a short message.');
}

if (mb_strlen($name) > 100 || mb_strlen($contact) > 150 || mb_strlen($company) > 150 || mb_strlen($message) > 3000) {
    reply(false, 'That message is a bit too long.');
}

db()->prepare('INSERT INTO enquiries (name, contact, company, message, ip, created_at) VALUES (?, ?, ?, ?, ?, ?)')
    ->execute([$name, $contact, $company, $message, $_SERVER['REMOTE_ADDR'] ?? '', date('Y-m-d H:i:s')]);

$_SESSION['last_enquiry'] = time();

reply(true, "Thanks $name, your message has been sent. I'll get back to you soon.");
