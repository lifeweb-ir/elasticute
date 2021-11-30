<?php

namespace ElastiCute\ElastiCute\Response;

/**
 * Class MappableResponse
 *
 * @package ElastiCute\ElastiCute\ElasticResponse
 */
class MappableResponse extends ElastiCuteResponse
{
	protected array $mappableResponse;

	/**
	 * MappableResponse constructor.
	 *
	 * @param array $elasticResponse
	 * @param array $mappableResponse
	 */
	public function __construct( array $elasticResponse, array $mappableResponse )
	{
		$this->mappableResponse = $mappableResponse;

		parent::__construct( $elasticResponse );
	}

	/**
	 * @param callable $action
	 *
	 * @return mixed
	 */
	public function map( callable $action )
	{
		return array_map( $action, $this->mappableResponse );
	}

	/**
	 * @return array
	 */
	public function toList(): array
    {
		return $this->mappableResponse;
	}
}
