<?php

namespace ElastiCute\ElastiCute\Response;

/**
 * Class ElastiCuteResponse
 *
 * @package ElastiCute\ElastiCute
 */
class ElastiCuteResponse
{
	protected array $elasticResponse;

	/**
	 * ElastiCuteResponse constructor.
	 *
	 * @param array $elasticResponse
	 */
	public function __construct( array $elasticResponse )
	{
		$this->elasticResponse = $elasticResponse;
	}

	protected function processResponse()
	{

	}

	/**
	 * Return response as array
	 *
	 * @return array
	 */
	public function toArray(): array
    {
		return $this->elasticResponse;
	}

	/**
	 * @return false|string
	 */
	public function toJson()
	{
		return json_encode( $this->elasticResponse, JSON_UNESCAPED_UNICODE );
	}

	/**
	 * @return array
	 */
	public function getElasticResponse() : array
	{
		return $this->elasticResponse;
	}
}
