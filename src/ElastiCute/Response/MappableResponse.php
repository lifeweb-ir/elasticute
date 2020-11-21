<?php

namespace ElastiCute\ElastiCute\Response;

use ElastiCute\ElastiCute\Response\ElastiCuteResponse;

/**
 * Class MappableResponse
 *
 * @package ElastiCute\ElastiCute\ElasticResponse
 */
class MappableResponse extends ElastiCuteResponse
{
	protected array $mappable_response;
	
	/**
	 * MappableResponse constructor.
	 *
	 * @param array $elastic_response
	 * @param array $mappable_response
	 */
	public function __construct( array $elastic_response, array $mappable_response )
	{
		$this->mappable_response = $mappable_response;
		
		parent::__construct( $elastic_response );
	}
	
	/**
	 * @param callable $action
	 *
	 * @return mixed
	 */
	public function map( callable $action )
	{
		return array_map( $action, $this->mappable_response );
	}
	
	/**
	 * @return array
	 */
	public function toList()
	{
		return $this->mappable_response;
	}
}