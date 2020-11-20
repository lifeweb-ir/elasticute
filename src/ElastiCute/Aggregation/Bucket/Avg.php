<?php

namespace ElastiCute\ElastiCute\Aggregation\Bucket;

use ElastiCute\ElastiCute\Aggregation\AggregateBuilder;
use ElastiCute\ElastiCute\Aggregation\AggregationQuery;

/**
 * Class Avg
 *
 * @package ElastiCute\ElastiCute\Aggregation\Bucket
 */
class Avg extends AggregateBuilder
{
	protected string $script_name;
	
	protected string $script_params;
	
	protected string $missing;
	
	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	public function build()
	{
		$aggregations_info = [];
		
		if ( isset( $this->field ) )
			$aggregations_info[$this->label]['avg']['field'] = $this->field;
		
		if ( isset( $this->script_name ) && isset( $this->script_params ) )
			$aggregations_info[$this->label]['avg']['script'] = [
				'id' => $this->script_name,
				'params' => $this->script_params,
			];
		
		if ( isset( $this->script_name ) && ! isset( $this->script_params ) )
			$aggregations_info[$this->label]['avg']['script'] = [
				'source' => $this->script_name,
			];
		
		if ( isset( $this->missing ) )
			$aggregations_info[$this->label]['avg']['missing'] = $this->missing;
		
		if ( isset( $this->inside ) )
			$aggregations_info[$this->label]['aggs'] = $this->doDeepJob( $this->inside );
		
		$this->query_builder->doInsideJob( $aggregations_info );
		
		return $this;
	}
}