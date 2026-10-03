<?php
require 'inc/boot.php';

crud_page([
    'table'    => 'education',
    'title'    => 'Education',
    'active'   => 'education',
    'singular' => 'education item',
    'intro'    => 'Degrees and certificates. The first one gets the green border.',
    'main'     => 'title',
    'sub'      => 'place',
    'fields'   => [
        ['name' => 'title', 'label' => 'Degree / course', 'type' => 'text', 'required' => true],
        ['name' => 'place', 'label' => 'College / institute', 'type' => 'text', 'half' => true],
        ['name' => 'period', 'label' => 'Years', 'type' => 'text', 'hint' => 'e.g. 2020 - 2023', 'half' => true],
        ['name' => 'pill', 'label' => 'Small tag (optional)', 'type' => 'text', 'hint' => 'e.g. Pursuing', 'half' => true],
        ['name' => 'sort', 'label' => 'Order', 'type' => 'number', 'half' => true],
    ],
]);
