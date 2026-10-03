<?php
require 'inc/boot.php';

crud_page([
    'table'    => 'steps',
    'title'    => 'How I work',
    'active'   => 'process',
    'singular' => 'step',
    'intro'    => 'The numbered steps in the "How I work" section. Delete all of them to hide the section.',
    'main'     => 'title',
    'sub'      => 'text',
    'fields'   => [
        ['name' => 'title', 'label' => 'Step title', 'type' => 'text', 'required' => true],
        ['name' => 'text', 'label' => 'Short text', 'type' => 'textarea', 'rows' => 3],
        ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'half' => true],
    ],
]);
