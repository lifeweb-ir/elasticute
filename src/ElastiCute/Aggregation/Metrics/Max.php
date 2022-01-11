<?php

namespace ElastiCute\ElastiCute\Aggregation\Metrics;

use ElastiCute\ElastiCute\Aggregation\Aggregate;

/**
 * Class Avg
 *
 * @package ElastiCute\ElastiCute\Aggregation\Bucket
 */
class Max extends Aggregate
{
    /**
     * @var string
     */
    protected string $aggregationName = 'max';

    /**
     * @return array
     */
	protected function generateQuery(): array
	{
		$aggregationBody = [];

		if ( isset( $this->field ) )
            $aggregationBody['max']['field'] = $this->field;

		return $aggregationBody;
	}
}
