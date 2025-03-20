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
    
    if (!$propertyId || !$relatedProperty || !$relatedValue) {
        return new JsonModel([]);
    }
    
    // Get property IDs if terms were provided
    if (!is_numeric($propertyId)) {
        $property = $this->api()->searchOne('properties', ['term' => $propertyId])->getContent();
        $propertyId = $property ? $property->id() : null;
    }
    
    if (!is_numeric($relatedProperty)) {
        $relProp = $this->api()->searchOne('properties', ['term' => $relatedProperty])->getContent();
        $relatedPropertyId = $relProp ? $relProp->id() : null;
    }
    
    if (!$propertyId || !$relatedPropertyId) {
        return new JsonModel([]);
    }
    
    // Use API search rather than direct DB query for first phase testing
    // Find items with the first property value
    $items = $this->api()->search('items', [
        'property' => [
            [
                'property' => $relatedProperty,
                'type' => 'eq',
                'text' => $relatedValue
            ]
        ],
        'limit' => 100 // Limit for testing
    ])->getContent();
    
    // Extract unique values for second property
    $values = [];
    foreach ($items as $item) {
        $propValues = $item->value($propertyId, ['all' => true]);
        foreach ($propValues as $value) {
            $textValue = trim($value->__toString());
            if (!empty($textValue) && !in_array($textValue, $values)) {
                $values[] = $textValue;
            }
        }
    }
    
    sort($values);
    return new JsonModel($values);
}
}