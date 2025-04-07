<?php
namespace UnitedSearch\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnitedSearch\View\Helper\DualPropertySearchForm;
use UnitedSearch\Service\PropertyValueService;

class DualPropertySearchFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DualPropertySearchForm(
            $container->get('UnitedSearch\PropertyValueService')
        );
    }
}