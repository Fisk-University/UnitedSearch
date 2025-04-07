<?php
namespace UnitedSearch\Mvc\Controller\Listener;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Http\Request;

class SearchListener extends AbstractListenerAggregate
{
    /**
     * @var \Laminas\Log\Logger
     */
    protected $logger;
    
    /**
     * @param \Laminas\Log\Logger $logger
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // Listen for the route event to intercept search requests
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_ROUTE,
            [$this, 'onRoute'],
            -100
        );
    }
    
    /**
     * Handle route event to clean up search parameters
     *
     * @param MvcEvent $event
     */
    public function onRoute(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        if (!$routeMatch) {
            return;
        }
        
        // Only process item browse route
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        
        if ($controller !== 'Omeka\Controller\Site\Item' || $action !== 'browse') {
            return;
        }
        
        $request = $event->getRequest();
        if (!$request instanceof Request) {
            return;
        }
        
        // Get query parameters
        $query = $request->getQuery()->toArray();
        $this->logger->info('UnitedSearch: Processing search request', ['query' => $query]);
        
        // Check for property search parameters
        if (isset($query['property']) && is_array($query['property'])) {
            $properties = $query['property'];
            $filteredProperties = [];
            
            // Filter out empty property searches
            foreach ($properties as $index => $propertySearch) {
                // Only include property searches with non-empty text
                if (isset($propertySearch['text']) && trim($propertySearch['text']) !== '') {
                    $this->logger->info('UnitedSearch: Including property search', [
                        'property' => $propertySearch['property'] ?? 'unknown',
                        'text' => $propertySearch['text']
                    ]);
                    $filteredProperties[] = $propertySearch;
                } else {
                    $this->logger->info('UnitedSearch: Filtering out empty property search', [
                        'property' => $propertySearch['property'] ?? 'unknown'
                    ]);
                }
            }
            
            // If we filtered out some property searches, update the query
            if (count($filteredProperties) !== count($properties)) {
                // Re-index the array
                $updatedProperties = array_values($filteredProperties);
                
                // Update the query
                if (empty($updatedProperties)) {
                    // If no properties with text, remove property search completely
                    unset($query['property']);
                    $this->logger->info('UnitedSearch: Removed empty property searches');
                } else {
                    // Otherwise update with the non-empty searches
                    $query['property'] = $updatedProperties;
                    $this->logger->info('UnitedSearch: Updated property searches', [
                        'count' => count($updatedProperties)
                    ]);
                }
                
                // Set the updated query parameters
                $request->getQuery()->fromArray($query);
                $this->logger->info('UnitedSearch: Updated search query parameters');
            }
        }
    }
}