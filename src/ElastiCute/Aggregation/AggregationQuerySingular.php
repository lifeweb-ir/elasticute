<?php

namespace ElastiCute\ElastiCute\Aggregation;

use ElastiCute\ElastiCute\Aggregation\Metrics\Avg;
use ElastiCute\ElastiCute\Aggregation\Bucket\Histogram;
use ElastiCute\ElastiCute\Aggregation\Bucket\Terms;
use ElastiCute\ElastiCute\Aggregation\Metrics\Max;

class AggregationQuerySingular
{
    /**
     * @var array
     */
    protected array $children = [];

    /**
     * @var array
     */
    protected array $query = [];

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var AggregationBuilder
     */
    protected AggregationBuilder $aggregationBuilder;

    /**
     * @var ?Aggregate
     */
    protected ?Aggregate $aggregate = null;

    /**
     * AggregationQuerySingular constructor.
     * @param string $name
     * @param AggregationBuilder $aggregationBuilder
     */
    public function __construct(string $name, AggregationBuilder $aggregationBuilder)
    {
        $this->aggregationBuilder = $aggregationBuilder;
        $this->name = $name;
    }

    /**
     * @return Avg
     */
    public function avg(): Avg
    {
        return $this->aggregate = new Avg($this);
    }

    /**
     * @return Terms
     */
    public function terms(): Terms
    {
        return $this->aggregate = new Terms($this);
    }

    /**
     * @return Histogram
     */
    public function histogram(): Histogram
    {
        return $this->aggregate = new Histogram($this);
    }

    /**
     * @return Max
     */
    public function max(): Max
    {
        return $this->aggregate = new Max($this);
    }

    /**
     * @return self[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param self $child
     */
    public function addChild(self $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    /**
     * @return AggregationBuilder
     */
    public function getAggregationBuilder(): AggregationBuilder
    {
        return $this->aggregationBuilder;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Aggregate|null
     */
    public function getAggregate(): ?Aggregate
    {
        return $this->aggregate;
    }
}
