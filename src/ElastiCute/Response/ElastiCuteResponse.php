<?php

namespace ElastiCute\ElastiCute\Aggregation\Response;

/**
 * Class ElastiCuteResponse
 *
 * @package ElastiCute\ElastiCute
 */
class ElastiCuteResponse
{
	protected array $elastic_response;
	
	/**
	 * ElastiCuteResponse constructor.
	 *
	 * @param array $elastic_response
	 */
	public function __construct( array $elastic_response )
	{
		$this->elastic_response = $elastic_response;
	}
	
	protected function processResponse()
	{
	
	}
	
	/**
	 * Return response as array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->elastic_response;
	}
	
	/**
	 * @return false|string
	 */
	public function toJson()
	{
		return json_encode( $this->elastic_response, JSON_UNESCAPED_UNICODE );
	}
	
	/**
	 * @return array
	 */
	public function getElasticResponse() : array
	{
		return $this->elastic_response;
	}
}