<?php
namespace UnitedSearch\Service\Controller\Site;

use Interop\Container\ContainerInterface;
use UnitedSearch\Controller\Site\PropertyValuesController;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PropertyValuesControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new PropertyValuesController();
    }
}