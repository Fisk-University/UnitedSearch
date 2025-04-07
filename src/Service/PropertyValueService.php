<?php
namespace UnitedSearch\Service;

use Doctrine\ORM\EntityManager;
use Omeka\Api\Manager as ApiManager;

class PropertyValueService
{
    private $entityManager;
    private $apiManager;
    private $cache;

    public function __construct(
        EntityManager $entityManager, 
        ApiManager $apiManager, 
        CacheInterface $cache
    ) {
        $this->entityManager = $entityManager;
        $this->apiManager = $apiManager;
        $this->cache = $cache;
    }

    /**
     * Retrieve unique property values with advanced caching and performance optimization
     *
     * @param string $propertyTerm Property term (e.g., 'dcterms:title')
     * @param array $options Additional retrieval options
     * @return array Unique property values
     */
    public function getUniquePropertyValues(string $propertyTerm, array $options = []): array
    {
        $cacheKey = "property_values_" . md5($propertyTerm . serialize($options));
        
        // Check cache first
        $cachedValues = $this->cache->getItem($cacheKey);
        if ($cachedValues !== null) {
            return $cachedValues;
        }

        // Default options
        $defaultOptions = [
            'limit' => 1000,
            'sort_by' => 'title',
            'sort_order' => 'asc',
            'distinct' => true
        ];
        $options = array_merge($defaultOptions, $options);

        // Optimized query using Doctrine QueryBuilder
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('DISTINCT v.value')
            ->from('Omeka\Entity\Value', 'v')
            ->innerJoin('v.property', 'p')
            ->where('p.term = :propertyTerm')
            ->andWhere('v.type = :valueType')
            ->setParameter('propertyTerm', $propertyTerm)
            ->setParameter('valueType', 'literal')
            ->setMaxResults($options['limit'])
            ->orderBy('v.value', $options['sort_order'] === 'asc' ? 'ASC' : 'DESC');

        $values = $queryBuilder->getQuery()->getScalarResult();
        
        // Flatten and clean values
        $uniqueValues = array_map('trim', array_column($values, 'value'));
        $uniqueValues = array_filter($uniqueValues);
        sort($uniqueValues);

        // Cache results
        $this->cache->setItem($cacheKey, $uniqueValues);

        return $uniqueValues;
    }

    /**
     * Create a relationship map between two properties
     *
     * @param string $propertyOne First property term
     * @param string $propertyTwo Second property term
     * @return array Relationship map
     */
    public function createRelationshipMap(string $propertyOne, string $propertyTwo): array
    {
        $cacheKey = "relationship_map_{$propertyOne}_{$propertyTwo}";
        
        // Check cache first
        $cachedMap = $this->cache->getItem($cacheKey);
        if ($cachedMap !== null) {
            return $cachedMap;
        }

        $relationshipMap = [];

        // Use Doctrine to create an efficient query
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('DISTINCT v1.value AS firstValue, v2.value AS secondValue')
            ->from('Omeka\Entity\Value', 'v1')
            ->innerJoin('Omeka\Entity\Value', 'v2', 'WITH', 'v1.resource = v2.resource')
            ->innerJoin('v1.property', 'p1')
            ->innerJoin('v2.property', 'p2')
            ->where('p1.term = :propertyOne')
            ->andWhere('p2.term = :propertyTwo')
            ->setParameter('propertyOne', $propertyOne)
            ->setParameter('propertyTwo', $propertyTwo);

        $results = $queryBuilder->getQuery()->getScalarResult();

        // Build relationship map
        foreach ($results as $result) {
            $firstValue = trim($result['firstValue']);
            $secondValue = trim($result['secondValue']);
            
            if (!empty($firstValue) && !empty($secondValue)) {
                $relationshipMap[$firstValue][] = $secondValue;
            }
        }

        // Remove duplicates and sort
        foreach ($relationshipMap as &$values) {
            $values = array_unique($values);
            sort($values);
        }

        // Cache results
        $this->cache->setItem($cacheKey, $relationshipMap);

        return $relationshipMap;
    }
}