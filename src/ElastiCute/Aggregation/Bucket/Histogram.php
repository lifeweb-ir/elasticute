<?php

namespace ElastiCute\ElastiCute\Aggregation\Bucket;

use ElastiCute\ElastiCute\Aggregation\Aggregate;

/**
 * Class Avg
 *
 * @package ElastiCute\ElastiCute\Aggregation\Bucket
 */
class Histogram extends Aggregate
{
    /**
     * @var string
     */
    protected string $aggregationName = 'histogram';

    /**
     * @return array
     */
    protected function generateQuery(): array
    {
        $aggregationBody = [];

        if (isset($this->field))
            $aggregationBody['histogram']['field'] = $this->field;

        return $aggregationBody;
    }
}
