<?php
namespace UnitedSearch;

return [
    'block_layouts' => [
        'factories' => [
            'ItemSetSearch' => Site\BlockLayout\ItemSetSearchFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
];