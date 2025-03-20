<?php
namespace UnitedSearch\Site\BlockLayout;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use UnitedSearch\Site\BlockLayout\ItemSetSearch;

class ItemSetSearchFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new ItemSetSearch(
            $services->get('FormElementManager'), 
            $services->get('Omeka\ApiManager')
        );
    }
}