<?php
namespace UnitedSearch\Controller\Site;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class PropertyValuesController extends AbstractActionController
{
    public function indexAction()
    {
        // Get parameters
        $propertyId = $this->params()->fromQuery('property_id');
        $relatedProperty = $this->params()->fromQuery('related_property');
        $relatedValue = $this->params()->fromQuery('related_value');
        
        // For testing, just return some dummy data
        $dummyValues = [
            "Value 1 for $propertyId related to $relatedValue",
            "Value 2 for $propertyId related to $relatedValue",
            "Value 3 for $propertyId related to $relatedValue"
        ];
        
        return new JsonModel($dummyValues);
    }
}