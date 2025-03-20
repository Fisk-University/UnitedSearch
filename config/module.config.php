<?php
namespace UnitedSearch;

return [
    'block_layouts' => [
        'factories' => [
            'ItemSetSearch' => Site\BlockLayout\ItemSetSearchFactory::class,
            'DualPropertySearch' => Site\BlockLayout\DualPropertySearchFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            'UnitedSearch\Controller\Site\PropertyValues' => Service\Controller\Site\PropertyValuesControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    'property-values' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/s/:site-slug/property-values',
                            'defaults' => [
                                '__NAMESPACE__' => 'UnitedSearch\Controller\Site',
                                'controller' => 'PropertyValues',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];