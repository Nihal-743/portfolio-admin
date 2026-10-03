<?php
require 'inc/boot.php';

crud_page([
    'table'    => 'jobs',
    'title'    => 'Experience',
    'active'   => 'experience',
    'singular' => 'job',
    'intro'    => 'Your jobs, newest first. Lower order number shows on top.',
    'main'     => 'title',
    'sub'      => 'period',
    'fields'   => [
        ['name' => 'title', 'label' => 'Job title', 'type' => 'text', 'required' => true],
        ['name' => 'company', 'label' => 'Company & city', 'type' => 'text', 'required' => true],
        ['name' => 'period', 'label' => 'Period', 'type' => 'text', 'hint' => 'e.g. Oct 2025 - Present', 'half' => true],
        ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'half' => true],
        ['name' => 'points', 'label' => 'What you did', 'type' => 'textarea', 'rows' => 7, 'hint' => 'One point per line.'],
        ['name' => 'is_current', 'label' => 'This is my current job (green dot)', 'type' => 'check'],
    ],
]);
