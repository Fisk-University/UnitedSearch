<?php
namespace UnitedSearch\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Renderer\PhpRenderer;
use UnitedSearch\Service\PropertyValueService;

class DualPropertySearchForm extends AbstractHelper
{
    /**
     * @var PropertyValueService
     */
    protected $propertyValueService;

    public function __construct(PropertyValueService $propertyValueService)
    {
        $this->propertyValueService = $propertyValueService;
    }

    /**
     * Render the dual property search form
     * 
     * @param array $options Configuration options for the search form
     * @return string Rendered HTML for the search form
     */
    public function __invoke(array $options = [])
    {
        $view = $this->getView();
        
        // Default options
        $defaultOptions = [
            'propertyOne' => null,
            'propertyTwo' => null,
            'joinType' => 'and',
        ];
        $options = array_merge($defaultOptions, $options);

        // Prepare data for the view
        $blockData = [
            'propertyOne' => $options['propertyOne'],
            'propertyTwo' => $options['propertyTwo'],
            'joinType' => $options['joinType'],
        ];

        // If both properties are selected, get values using the service
        if ($options['propertyOne'] && $options['propertyTwo']) {
            $blockData['propertyOneValues'] = $this->propertyValueService->getUniquePropertyValues(
                $options['propertyOne'], 
                ['limit' => 500]
            );

            $blockData['relationshipMap'] = $this->propertyValueService->createRelationshipMap(
                $options['propertyOne'], 
                $options['propertyTwo']
            );

            // Get all possible values for property two if join type is 'or'
            if ($options['joinType'] === 'or') {
                $blockData['propertyTwoValues'] = $this->propertyValueService->getUniquePropertyValues(
                    $options['propertyTwo'], 
                    ['limit' => 500]
                );
            }
        }

        // Render the form using the existing partial
        return $view->partial('common/block-layout/dualproperty-search', $blockData);
    }
}