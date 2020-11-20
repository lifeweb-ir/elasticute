<?php

namespace ElastiCute\ElastiCute\Aggregation;

/**
 * Class AggregateBuilder
 *
 * @package ElastiCute\ElastiCute\Aggregation
 */
class AggregateBuilder
{
	protected AggregationQuery $query_builder;
	
	protected string $type;
	
	protected string $field;
	
	protected string $label;
	
	protected string $inside;
	
	/** @var bool $is_deep */
	protected bool $is_deep = false;
	
	/** @var array $current_depth_info */
	protected static array $current_depth_info = [
		0 => [
			'aggs' => [],
		],
	];
	
	public function __construct( AggregationQuery $query_builder )
	{
		$this->query_builder = $query_builder;
	}
	
	/**
	 * @param string $field
	 */
	public function field( string $field ) : void
	{
		$this->field = $field;
	}
	
	/**
	 * @param string $label
	 */
	public function label( string $label ) : void
	{
		$this->label = $label;
	}
	
	/**
	 *
	 */
	public function build()
	{
	
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
}