<?php
namespace UnitedSearch\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnitedSearch\Service\PropertyValueService;
use Laminas\Cache\Storage\Adapter\Memory as MemoryCache;

class PropertyValueServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('Omeka\EntityManager');
        $apiManager = $container->get('Omeka\ApiManager');
        
        // Use Memory cache by default, can be replaced with Redis/Filesystem cache
        $cache = new MemoryCache([
            'memory_limit' => 32 * 1024 * 1024,  // 32MB cache limit
        ]);

        return new PropertyValueService(
            $entityManager,
            $apiManager,
            $cache
        );
    }
}