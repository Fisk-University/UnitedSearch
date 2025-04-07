<?php
namespace UnitedSearch\Site\BlockLayout;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnitedSearch\Service\PropertyValueService;

class DualPropertySearchFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new DualPropertySearch(
            $services->get('FormElementManager'),
            $services->get('UnitedSearch\PropertyValueService')
        );
    }
}