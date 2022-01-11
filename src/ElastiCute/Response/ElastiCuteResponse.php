<?php

namespace ElastiCute\ElastiCute\Response;

/**
 * Class ElastiCuteResponse
 *
 * @package ElastiCute\ElastiCute
 */
class ElastiCuteResponse
{
    /**
     * @var array
     */
    protected array $elasticResponse;

    /**
     * @var array
     */
    protected array $mappableResponse;

	/**
	 * ElastiCuteResponse constructor.
	 *
	 * @param array $elasticResponse
	 */
	public function __construct( array $elasticResponse )
	{
		$this->elasticResponse = $elasticResponse;
		$this->mappableResponse = $elasticResponse['hits']['hits'] ?? [];
	}

    /**
     *
     */
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

    /**
     * @return array|mixed
     */
    public function getAggregations()
    {
        return $this->getElasticResponse()['aggregations'] ?? [];
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
