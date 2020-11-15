<?php

namespace ElastiCute\ElastiCute;

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
		];
		$args         = array_merge( $default_args, $args );
		
		switch ( true ) {
			case ( isset( $args[ 'field' ] ) ):
				$this->aggregates[ $args[ 'label' ] ][ 'avg' ][ 'field' ] = $args[ 'field' ];
				continue;
			case ( isset( $args[ 'script_name' ] ) && isset( $args[ 'script_params' ] ) ):
				$this->aggregates[ $args[ 'label' ] ][ 'avg' ][ 'script' ] = [
					'id' => $args[ 'script_name' ],
					'params' => $args[ 'script_params' ],
				];
				continue;
			case ( isset( $args[ 'script_name' ] ) && !isset( $args[ 'script_params' ] ) ):
				$this->aggregates[ $args[ 'label' ] ][ 'avg' ][ 'script' ] = [
					'source' => $args[ 'script_name' ],
				];
				continue;
			case ( isset( $args[ 'missing' ] ) ):
				$this->aggregates[ $args[ 'label' ] ][ 'avg' ][ 'missing' ] = $args[ 'missing' ];
				continue;
		}
		
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getAggregationList()
	{
		return $this->aggregates;
	}
}