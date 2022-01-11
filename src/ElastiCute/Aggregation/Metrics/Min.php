<?php

namespace ElastiCute\ElastiCute\Aggregation\Metrics;

use ElastiCute\ElastiCute\Aggregation\Aggregate;

/**
 * Class Avg
 *
 * @package ElastiCute\ElastiCute\Aggregation\Bucket
 */
class Min extends Aggregate
{
    /**
     * @var string
     */
    protected string $aggregationName = 'min';

    /**
     * @return array
     */
	protected function generateQuery(): array
	{
		$aggregationBody = [];

		if ( isset( $this->field ) )
            $aggregationBody['min']['field'] = $this->field;

		return $aggregationBody;
	}
}
