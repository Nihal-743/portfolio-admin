<?php
// opens the resume pdf in the browser and counts the view
require 'inc/functions.php';

$file = __DIR__ . '/cv/Nihal_Resume.pdf';

if (!file_exists($file)) {
    http_response_code(404);
    echo 'Resume not found.';
    exit;
}

track_cv_view();

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Nihal_Resume.pdf"');
header('Content-Length: ' . filesize($file));
header('Cache-Control: no-store');
readfile($file);
