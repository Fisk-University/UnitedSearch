<?php
namespace UnitedSearch\Service\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnitedSearch\Service\PropertyValueService;
use UnitedSearch\Service\CacheInterface;

class SimpleCache implements CacheInterface
{
    private $storage = [];

    public function getItem($key)
    {
        return $this->storage[$key] ?? null;
    }

    public function setItem($key, $value)
    {
        $this->storage[$key] = $value;
        return true;
    }

    public function hasItem($key)
    {
        return isset($this->storage[$key]);
    }

    public function removeItem($key)
    {
        unset($this->storage[$key]);
        return true;
    }
}

class PropertyValueServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $entityManager = $services->get('Omeka\EntityManager');
        $apiManager = $services->get('Omeka\ApiManager');
        
        // Create a simple cache that implements CacheInterface
        $cache = new SimpleCache();

        return new PropertyValueService(
            $entityManager,
            $apiManager,
            $cache
        );
    }
}