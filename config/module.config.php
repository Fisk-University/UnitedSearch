<?php
namespace UnitedSearch;
use Psr\Container\ContainerInterface;
use UnitedSearch\View\Helper\LocationMap;

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
            LocationMap::class => function (ContainerInterface $container) {
                $conn = $container->get('Omeka\Connection'); // Doctrine\DBAL\Connection
                return new LocationMap($conn);
            },
        ],
        'aliases' => [
            'locationMap' => LocationMap::class,
        ],
    ],
];