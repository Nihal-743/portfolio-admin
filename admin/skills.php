<?php
require 'inc/boot.php';

crud_page([
    'table'    => 'skill_groups',
    'title'    => 'Skills',
    'active'   => 'skills',
    'singular' => 'skill group',
    'intro'    => 'Each group is one row on the site (Front-end, Tools...).',
    'main'     => 'name',
    'sub'      => 'label',
    'fields'   => [
        ['name' => 'name', 'label' => 'Group name', 'type' => 'text', 'required' => true, 'half' => true],
        ['name' => 'label', 'label' => 'Small green label (optional)', 'type' => 'text', 'hint' => 'e.g. Main stack', 'half' => true],
        ['name' => 'items', 'label' => 'Skills', 'type' => 'textarea', 'rows' => 9,
         'hint' => 'One skill per line. Add * at the end to highlight it (e.g. HTML5 *). Add (basic) for the basic tag (e.g. PHP (basic)).'],
        ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'half' => true],
    ],
]);
