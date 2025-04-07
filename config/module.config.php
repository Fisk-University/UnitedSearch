<?php
namespace UnitedSearch;

return [
    'block_layouts' => [
        'factories' => [
            'ItemSetSearch' => Site\BlockLayout\ItemSetSearchFactory::class,
            'DualPropertySearch' => Site\BlockLayout\DualPropertySearchFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'dualPropertySearchForm' => Service\ViewHelper\DualPropertySearchFormFactory::class,
        ],
    ],
];
