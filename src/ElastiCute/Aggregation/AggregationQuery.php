<?php

namespace ElastiCute\ElastiCute\Aggregation;

use ElastiCute\ElastiCute\Aggregation\Bucket\Avg;
use ElastiCute\ElastiCute\QueryBuilder;

/**
 * Class AggregationQuery
 *
 * @package ElastiCute\ElastiCute
 */
class AggregationQuery
{
	/** @var QueryBuilder $query_builder */
	protected QueryBuilder $query_builder;
	
	/** @var array $aggregates */
	protected array $aggregates = [];
	
	/** @var bool $is_deep */
	protected bool $is_deep = false;
	
	/** @var array $current_depth_info */
	protected static array $current_depth_info = [
		0 => [
			'aggs' => [],
		],
	];
	
	/**
	 * AggregationQuery constructor.
	 *
	 * @param QueryBuilder $query_builder
	 */
	public function __construct( QueryBuilder $query_builder )
	{
		$this->query_builder = $query_builder;
	}
	
	/**
	 * @return Avg
	 */
	public function avg()
	{
		return new Avg( $this );
	}
	
	/**
	 * @param string $label
	 * @param string $field
	 *
	 * @return $this
	 */
	public function avgBasic( string $label, string $field )
	{
		return $this->doAvg( compact( 'label', 'field' ) );
	}
	
	/**
	 * @param string    $label
	 * @param string    $field
	 * @param float|int $missing
	 *
	 * @return $this
	 */
	public function avgAdvanced( string $label, string $field, float $missing = 0 )
	{
		return $this->doAvg( compact( 'label', 'field', 'missing' ) );
	}
	
	/**
	 * @param string     $label
	 * @param string     $script_name
	 * @param array|null $script_params [optional]
	 *
	 * @return $this
	 */
	public function avgScript( string $label, string $script_name, array $script_params = null )
	{
		return $this->doAvg( compact( 'label', 'script_name', 'script_params' ) );
	}
	
	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	protected function doAvg( array $args )
	{
		$default_args = [
			'label' => null,
			'field' => null,
			'script_name' => null,
			'script_params' => null,
			'missing' => null,
			'inside' => null,
		];
		$args = array_merge( $default_args, $args );
		$aggregations_info = [];
		
		if ( isset( $args['field'] ) )
			$aggregations_info[$args['label']]['avg']['field'] = $args['field'];
		if ( isset( $args['script_name'] ) && isset( $args['script_params'] ) )
			$aggregations_info[$args['label']]['avg']['script'] = [
				'id' => $args['script_name'],
				'params' => $args['script_params'],
			];
		if ( isset( $args['script_name'] ) && ! isset( $args['script_params'] ) )
			$aggregations_info[$args['label']]['avg']['script'] = [
				'source' => $args['script_name'],
			];
		if ( isset( $args['missing'] ) )
			$aggregations_info[$args['label']]['avg']['missing'] = $args['missing'];
		if ( isset( $args['inside'] ) )
			$aggregations_info[$args['label']]['aggs'] = $this->doDeepJob( $args['inside'] );
		
		$this->doInsideJob( $aggregations_info );
		
		return $this;
	}
	
	/**
	 * @param $agg_info
	 */
	protected function doInsideJob( $agg_info )
	{
		$current_info_count = count( $this::$current_depth_info );
		
		if ( $this->is_deep ) {
			self::$current_depth_info[$current_info_count - 1]['aggs'] = $agg_info;
		} else {
			$this->aggregates = array_merge( $this->aggregates, $agg_info );
		}
	}
	
	/**
	 * @param callable $closure
	 *
	 * @return array
	 */
	protected function doDeepJob( callable $closure )
	{
		$current_info_count = count( $this::$current_depth_info );
		$is_already_in_deep = $this->is_deep;
		$this->is_deep = true;
		
		$this::$current_depth_info[$current_info_count] = [
			'aggs' => [],
		];
		
		$closure( $this );
		
		if ( ! $is_already_in_deep ) {
			$this->is_deep = false;
		}
		
		return $this::$current_depth_info[$current_info_count]['aggs'] ?: [];
	}
	
	protected function makeProtectedInstance()
	{
	
	}
	
	/**
	 * @return array
	 */
	public function getAggregationList()
	{
		return $this->aggregates;
	}
}