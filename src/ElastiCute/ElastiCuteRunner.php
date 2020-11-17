<?php

namespace ElastiCute\ElastiCute;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use ElastiCute\ElastiCute\Response\ElastiCuteResponse;
use ElastiCute\ElastiCute\Response\MappableResponse;

/**
 * Class ElastiCuteRunner
 *
 * @package ElastiCute\ElastiCute
 */
class ElastiCuteRunner
{
	protected Client $official_builder;
	
	/**
	 * ElastiCuteRunner constructor.
	 */
	public function __construct()
	{
		$this->official_builder = ClientBuilder::create()->build();
	}
	
	/**
	 * @param array $params
	 *
	 * @return MappableResponse
	 * @throws ElastiCuteException
	 */
	public function search( array $params )
	{
		try {
			$search = $this->official_builder->search( $params );
			
			return new MappableResponse( $search, $search['hits']['hits'] ?? [] );
		} catch ( BadRequest400Exception $exception ) {
			$this->manageException( $exception );
		}
	}
	
	/**
	 * @param array $params
	 * @param bool  $source_only
	 *
	 * @return ElastiCuteResponse
	 * @throws ElastiCuteException
	 */
	public function find( array $params, bool $source_only )
	{
		$method = $source_only ? 'getSource' : 'get';
		
		try {
			$search = $this->official_builder->$method( $params );
			
			return new ElastiCuteResponse( $search );
		} catch ( BadRequest400Exception $exception ) {
			$this->manageException( $exception );
		}
	}
	
	/**
	 * @param array $params
	 *
	 * @return ElastiCuteResponse
	 * @throws ElastiCuteException
	 */
	public function mapping( array $params )
	{
		try {
			$search = $this->official_builder->indices()->getMapping( $params );
			
			return new ElastiCuteResponse( $search );
		} catch ( BadRequest400Exception $exception ) {
			$this->manageException( $exception );
		}
	}
	
	/**
	 * @param \Exception $exception
	 *
	 * @throws ElastiCuteException
	 */
	protected function manageException( \Exception $exception )
	{
		$error_message = json_decode( $exception->getMessage(), true );
		
		throw new ElastiCuteException( $error_message['error']['root_cause'][0]['reason'] ?? $exception->getMessage(), 400 );
	}
}