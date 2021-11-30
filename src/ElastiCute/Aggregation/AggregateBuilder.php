<?php

namespace ElastiCute\ElastiCute\Aggregation;

use ElastiCute\ElastiCute\QueryBuilder;

/**
 * Class AggregateBuilder
 *
 * @package ElastiCute\ElastiCute\Aggregation
 */
class AggregateBuilder
{
	protected AggregationQuery $query_builder;

	/** @var array $aggregates */
	protected array $aggregates = [];

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
	 *
	 * @return $this
	 */
	public function field( string $field ) : self
	{
		$this->field = $field;

		return $this;
	}

	/**
	 * @param string $label
	 *
	 * @return $this
	 */
	public function label( string $label ) : self
	{
		$this->label = $label;

		return $this;
	}

	/**
	 *
	 */
	public function build()
	{
		return new ProtectedAggregate( $this->query_builder );
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
			$this->query_builder->setAggregationList( array_merge( $this->query_builder->getAggregationList(), $agg_info ) );
		}
	}

	/**
	 * @param callable $closure
	 *
	 * @return array
	 */
	protected function doDeepJob( callable $closure ): array
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
