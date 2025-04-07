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
    'service_manager' => [
        'factories' => [
            'UnitedSearch\PropertyValueService' => Service\Factory\PropertyValueServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'dualPropertySearchForm' => View\Helper\Factory\DualPropertySearchFormFactory::class,
        ],
    ],
];