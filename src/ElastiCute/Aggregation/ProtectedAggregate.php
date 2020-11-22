<?php

namespace ElastiCute\ElastiCute\Aggregation;

use ElastiCute\ElastiCute\QueryBuilder;

/**
 * Class ProtectedAggregate
 *
 * @package ElastiCute\ElastiCute\Aggregation
 */
class ProtectedAggregate extends AggregationQuery
{
	public AggregationQuery $query_ref;
	
	/**
	 * ProtectedAggregate constructor.
	 *
	 * @param $query_ref
	 */
	public function __construct( AggregationQuery $query_ref )
	{
		$this->query_ref = $query_ref;
	}
}