<?php

namespace ElastiCute\ElastiCute;

use Composer\Autoload\ClassLoader;
use Dotenv\Dotenv;
use Elasticsearch\ClientBuilder;
use ElastiCute\ElastiCute\Aggregation\AggregationQuery;
use ElastiCute\ElastiCute\Aggregation\Response\ElastiCuteResponse;

/**
 * Class QueryBuilder
 *
 * @package ElastiCute\ElastiCute
 * @author  Payam Jafari/payamjafari.ir
 * @see     http://payamweber.github.io/elasticute
 */
class QueryBuilder
{
	use ElastiCuteFilters;
	
	/**
	 * Database credentials
	 */
	protected string $db_address;
	protected string $db_port;
	protected string $index_name;
	protected string $db_user;
	protected string $db_pass;
	
	/** @var bool $connected is database connected successfully */
	protected bool $connected = false;
	
	/**
	 * @var array $query_where
	 */
	protected array $query_where = [];
	
	/**
	 * @var array $query_sort
	 */
	protected array $query_sort = [];
	
	/** @var array $query_aggregation */
	protected array $query_aggregation = [];
	
	/**
	 * @var array $query_select
	 */
	protected array $query_select = [];
	
	/**
	 * @var bool $is_group_where
	 */
	protected bool $is_group_where = false;
	
	/** @var array $current_depth_info */
	protected static array $current_depth_info = [
		0 => [
			'type' => 'must',
			'conditions' => [],
		],
	];
	
	/**
	 * @var \Elasticsearch\Client $elastic
	 */
	protected $elastic;
	
	/**
	 * this is allowed operators for queries
	 */
	protected const ALLOWED_OPERATORS = [ 'term', 'match', 'match_all' ];
	
	/**
	 * Model constructor.
	 */
	public function __construct()
	{
		$ref = new \ReflectionClass( ClassLoader::class );
		$envreader = Dotenv::createImmutable( dirname( $ref->getFileName() ) . '/../../' );
		$envreader = $envreader->safeLoad();

//		$this->db_address = self::getEnv( 'ELCUTE_DB_ADDRESS', '127.0.0.1' );
//		$this->db_port    = self::getEnv( 'ELCUTE_DB_PORT', '27017' );
//		$this->index_name = self::getEnv( 'ELCUTE_DB_NAME', '' );
//		$this->db_user    = self::getEnv( 'ELCUTE_DB_USERNAME', '' );
//		$this->db_pass    = self::getEnv( 'ELCUTE_DB_PASSWORD', '' );

//		$userpass      = $this->db_user ? "{$this->db_user}:{$this->db_pass}@" : '';
		
		try {
			$this->connected = true;
		} catch ( \Exception $e ) {
			$this->connected = false;
		}
	}
	
	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed|static
	 */
	public static function __callStatic( $name, $arguments )
	{
		$self = new static();
		
		if ( ! $name ) {
			return $self;
		}
		
		return call_user_func_array( [ $self, $name ], $arguments );
	}
	
	/**
	 * Start the query builder
	 *
	 * @return static
	 */
	public static function query() : self
	{
		return self::__callStatic( '', [] );
	}
	
	/**
	 * @param        $key
	 * @param string $value
	 * @param string $operator
	 *
	 * @return $this
	 */
	protected function doWhere( $key, $value = '', $operator = 'match' )
	{
		$operator = in_array( $operator, self::ALLOWED_OPERATORS ) ? $operator : 'match';
		
		if ( $key ) {
			$this->addWhereCondition( $key, $value, $operator );
		}
		
		return $this;
	}
	
	/**
	 * @param        $key
	 * @param        $value
	 * @param string $operator
	 * @param array  $extra
	 */
	protected function addWhereCondition( $key, $value, $operator = 'match', array $extra = [] )
	{
		$current_info_count = count( $this::$current_depth_info );
//		if ( $this->query_where ) {
//			if ( $type == 'should' && $this::$current_depth_info[ 'type' ] == 'must' ) {
//				if ( $this->is_group_where ) {
//					$this::$current_depth_info[ 'type' ] = 'should';
//				} else {
//					$this->query_where[ 'should' ] = $this->query_where[ 'must' ];
//					unset( $this->query_where[ 'must' ] );
//				}
//			}
//		} else {
//			$this->query_where[ $type ] = [];
//		}
		// set group where conditions
		$condition_query = [
			$operator => [
				$key => $value,
			],
		];
		
		if ( $this->is_group_where ) {
			$this::$current_depth_info[$current_info_count - 1]['conditions'][] = $condition_query;
		} else {
			$this->query_where[] = $condition_query;
		}
	}
	
	/**
	 * @param array $fields
	 * @param string shouldder
	 *
	 * @return $this
	 */
	public function sort( array $fields )
	{
		$this->query_sort = $fields;
		
		return $this;
	}
	
	/**
	 * @param array $fields
	 *
	 * @return $this
	 */
	public function select( array $fields )
	{
		$this->query_select = $fields;
		
		return $this;
	}
	
	/**
	 * @param callable $filters
	 * @param string   $operator
	 *
	 * @return $this
	 */
	protected function boolianGroup( callable $filters, $operator = 'must' )
	{
		$current_info_count = count( $this::$current_depth_info );
		$is_already_in_group = $this->is_group_where;
		
		$this->is_group_where = true;
		$this::$current_depth_info[$current_info_count] = [
			'type' => $operator,
			'conditions' => [],
		];
		$filters( $this );
		if ( $is_already_in_group ) {
			$this::$current_depth_info[$current_info_count - 1]['conditions'][] = [
				'bool' => [
					$this::$current_depth_info[$current_info_count]['type'] =>
						$this::$current_depth_info[$current_info_count]['conditions'],
				],
			];
		} else {
			$this->is_group_where = false;
			
			$this->query_where[]['bool'] = [
				$this::$current_depth_info[$current_info_count]['type'] =>
					$this::$current_depth_info[$current_info_count]['conditions'],
			];
		}
		
		return $this;
	}
	
	/**
	 * @param      $id
	 * @param bool $source_only
	 *
	 * @return ElastiCuteResponse
	 * @throws ElastiCuteException
	 */
	public function find( $id, bool $source_only = true )
	{
		$this->initializeDatabaseAndCollection();
		
		$runner = new ElastiCuteRunner();
		
		return $runner->find( [
			'index' => $this->index_name,
			'id' => $id,
			'_source' => $this->query_select,
		], $source_only );
	}
	
	/**
	 * @param int $count
	 *
	 * @return Response\MappableResponse
	 * @throws ElastiCuteException
	 */
	public function get( int $count = 10 )
	{
		return $this->doGet( $count );
	}
	
	/**
	 * @param int $document_per_page
	 * @param int $page_number
	 *
	 * @return Response\MappableResponse
	 * @throws ElastiCuteException
	 */
	public function paginate( int $document_per_page = 10, $page_number = 1 )
	{
		return $this->doGet( $document_per_page, true, $page_number );
	}
	
	/**
	 * @return array
	 */
	public function generateBody()
	{
		return [
			'index' => $this->index_name,
			'body' => [
					'query' => [ 'bool' => [ 'must' => $this->query_where ] ] ?: [
						'match_all' => (object) [],
					],
					'sort' => $this->query_sort ?: (object) [],
					'size' => intval( $count ?: -1 ),
					'_source' => $this->query_select,
				]
				+ ( $paginate ? [
					'from' => $page_number * $count,
				] : [] )
				+ ( $this->query_aggregation ? [
					'aggs' => $this->query_aggregation,
				] : [] ),
		];
	}
	
	/**
	 * @param int  $count
	 * @param bool $paginate
	 * @param int  $page_number
	 *
	 * @return Response\MappableResponse
	 * @throws ElastiCuteException
	 */
	protected function doGet( $count = 10, bool $paginate = false, $page_number = 1 )
	{
		$this->initializeDatabaseAndCollection();
		
		$runner = new ElastiCuteRunner();
		
		return $runner->search( [
			'index' => $this->index_name,
			'body' => [
					'query' => [ 'bool' => [ 'must' => $this->query_where ] ] ?: [
						'match_all' => (object) [],
					],
					'sort' => $this->query_sort ?: (object) [],
					'size' => intval( $count ?: -1 ),
					'_source' => $this->query_select,
				]
				+ ( $paginate ? [
					'from' => $page_number * $count,
				] : [] )
				+ ( $this->query_aggregation ? [
					'aggs' => $this->query_aggregation,
				] : [] ),
		] );
	}
	
	/**
	 * @param       $name
	 * @param mixed $default
	 *
	 * @return array|false|string|null
	 */
	protected static function getEnv( $name, $default = null )
	{
		return isset( $_ENV[$name] ) ? $_ENV[$name] : $default;
	}
	
	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function index( string $name )
	{
		if ( isset( $this ) ) {
			$self = $this;
		} else {
			$self = new static();
		}
		
		$self->index_name = $name;
		return $self;
	}
	
	/**
	 * @throws ElastiCuteException
	 */
	protected function initializeDatabaseAndCollection()
	{
		if ( ! $this->connected ) {
			throw new ElastiCuteException( 'Could not connect to database' );
		}
		
		if ( ! $this->index_name ) {
			throw new ElastiCuteException( 'Index name has not been set' );
		}
	}
	
	/**
	 * @return ElastiCuteResponse
	 * @throws ElastiCuteException
	 */
	public function mapping()
	{
		$this->initializeDatabaseAndCollection();
		
		$runner = new ElastiCuteRunner();
		
		return $runner->mapping( [
			'index' => $this->index_name,
		] );
	}
	
	/**
	 * @param callable $aggregations
	 *
	 * @return $this
	 */
	public function aggregate( callable $aggregations )
	{
		$aggregation_query = new AggregationQuery( $this );
		$aggregations( $aggregation_query );
		$this->query_aggregation = $aggregation_query->getAggregationList();
		return $this;
	}
	
	/**
	 * Die and dump
	 * Developer Only
	 *
	 * @param $var
	 */
	public static function dieAndDump( $var )
	{
		if ( is_array( $var ) ) {
			header( 'Content-Type: application/json' );
			echo json_encode( $var );
			die();
		} else {
			echo "<pre>";
			var_dump( $var );
			die();
		}
	}
}