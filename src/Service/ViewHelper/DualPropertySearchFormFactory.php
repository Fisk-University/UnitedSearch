<?php
namespace UnitedSearch\Service\ViewHelper;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use UnitedSearch\View\Helper\DualPropertySearchForm;

class DualPropertySearchFormFactory implements FactoryInterface
{
    /**
     * Create the DualPropertySearchForm view helper
     *
     * @param ContainerInterface $services
     * @param string $requestedName
     * @param array|null $options
     * @return DualPropertySearchForm
     */
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        // Get the necessary services
        $formElementManager = $services->get('FormElementManager');
        $urlHelper = $services->get('ViewHelperManager')->get('url');
        $logger = $services->get('Omeka\Logger');
        $apiManager = $services->get('Omeka\ApiManager');
        
        // Log when the factory is called
        $logger->info('UnitedSearch: Creating DualPropertySearchForm view helper');
        
        return new DualPropertySearchForm(
            $formElementManager,
            $urlHelper,
            $logger,
            $apiManager
        );
    }
}