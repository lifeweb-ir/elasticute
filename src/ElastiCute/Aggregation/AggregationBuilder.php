<?php

namespace ElastiCute\ElastiCute\Aggregation;

use ElastiCute\ElastiCute\Aggregation\Bucket\Avg;
use ElastiCute\ElastiCute\QueryBuilder;

/**
 * Class AggregationQuery
 *
 * @package ElastiCute\ElastiCute
 */
class AggregationBuilder
{
    /**
     * @var QueryBuilder $queryBuilder
     */
    protected QueryBuilder $queryBuilder;

    /**
     * @var array $aggregates
     */
    protected array $aggregates = [];

    /**
     * @var AggregationQuerySingular
     */
    protected AggregationQuerySingular $currentGroup;

    /**
     * AggregationQuery constructor.
     *
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param string $name
     * @return AggregationQuerySingular
     */
    public function make(string $name): AggregationQuerySingular
    {
        return $this->aggregates[] = $this->currentGroup = new AggregationQuerySingular($name, $this);
    }

    /**
     * @return AggregationQuerySingular
     */
    public function getCurrentGroup(): AggregationQuerySingular
    {
        return $this->currentGroup;
    }

    /**
     * @param AggregationQuerySingular $currentGroup
     */
    public function setCurrentGroup(AggregationQuerySingular $currentGroup): void
    {
        $this->currentGroup = $currentGroup;
    }

    /**
     * @param AggregationQuerySingular|null $group
     * @return array
     */
    public function generateAggregations(?AggregationQuerySingular $group = null): array
    {
        $aggregations = [];

        foreach (($group ? $group->getChildren() : $this->aggregates) as $key => $aggregate) {
            if ($aggregate instanceof AggregationQuerySingular) {
                if ($aggregate->getChildren()) {
                    $aggregations[$aggregate->getName()] = array_merge($aggregate->getAggregate()->build(), [
                        'aggs' => $this->generateAggregations($aggregate)
                    ]);
                } else {
                    $aggregations[$aggregate->getName()] = $aggregate->getAggregate()->build();
                }
            }
        }

        return $aggregations;
    }

    /**
     * @return array
     */
    public function getAggregationList(): array
    {
        return $this->aggregates;
    }

    /**
     * @param array $aggregates
     */
    public function setAggregationList(array $aggregates)
    {
        $this->aggregates = $aggregates;
    }
}
