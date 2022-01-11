<?php

namespace ElastiCute\ElastiCute\Aggregation;

/**
 * Class AggregateBuilder
 *
 * @package ElastiCute\ElastiCute\Aggregation
 */
class Aggregate
{
    /**
     * @var AggregationQuerySingular
     */
    protected AggregationQuerySingular $aggregationQuerySingular;

    /**
     * @var string
     */
    protected string $aggregationName = 'name';

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var string
     */
    protected string $field;

    /**
     * @var array
     */
    protected array $additionalOptions = [];

    /**
     * Aggregate constructor.
     * @param AggregationQuerySingular $aggregationQuerySingular
     */
    public function __construct(AggregationQuerySingular $aggregationQuerySingular)
    {
        $this->aggregationQuerySingular = $aggregationQuerySingular;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function field(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return array
     */
    protected function generateQuery(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return array_merge_recursive(
            $this->generateQuery(),
            [
                $this->getAggregationName() => $this->additionalOptions
            ]
        );
    }

    /**
     * @param callable $aggregations
     * @return $this
     */
    public function aggregations(callable $aggregations): self
    {
        $oldGroup = $this->getAggregationQuerySingular()->getAggregationBuilder()->getCurrentGroup();

        $this->getAggregationQuerySingular()->getAggregationBuilder()->setCurrentGroup($this->getAggregationQuerySingular());

        $aggregations($this->getAggregationQuerySingular()->getAggregationBuilder());

        $this->getAggregationQuerySingular()->getAggregationBuilder()->setCurrentGroup($oldGroup);

        return $this;
    }

    /**
     * @return AggregationQuerySingular
     */
    public function getAggregationQuerySingular(): AggregationQuerySingular
    {
        return $this->aggregationQuerySingular;
    }

    /**
     * @param array $additionalOptions
     * @return self
     */
    public function additionalOptions(array $additionalOptions): self
    {
        $this->additionalOptions = $additionalOptions;
        return $this;
    }

    /**
     * @return string
     */
    protected function getAggregationName(): string
    {
        return $this->aggregationName;
    }
}
