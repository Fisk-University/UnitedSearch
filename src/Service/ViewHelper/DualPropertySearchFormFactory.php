<?php
namespace UnitedSearch\Service\ViewHelper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnitedSearch\View\Helper\DualPropertySearchForm;

class DualPropertySearchFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $viewHelperManager = $container->get('ViewHelperManager');
        $apiHelper = $viewHelperManager->get('api');

        return new DualPropertySearchForm($apiHelper);
    }
}
