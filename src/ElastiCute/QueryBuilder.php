<?php

namespace ElastiCute\ElastiCute;

use Composer\Autoload\ClassLoader;
use Dotenv\Dotenv;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;

/**
 * Class QueryBuilder
 *
 * @method QueryBuilder index( $name )
 * @method QueryBuilder table( $name )
 * @method QueryBuilder aggregate( callable $aggregations )
 * @method QueryBuilder groupShould( callable $filters )
 * @method QueryBuilder groupMust( callable $filters )
 * @method QueryBuilder groupMustNot( callable $filters )
 * @method QueryBuilder groupFilter( callable $filters )
 * @method QueryBuilder whereContains( string $name, $value = '', bool $match_phrase = false )
 * @method QueryBuilder whereNotContains( string $name, $value = '', bool $match_phrase = false )
 * @method QueryBuilder whereEqual( string $name, $value )
 * @method QueryBuilder whereNotEqual( string $name, $value )
 * @method QueryBuilder whereExists( string $name )
 * @method QueryBuilder whereNotExists( string $name )
 * @method QueryBuilder select( array $fields ) select fields from collection
 * @method QueryBuilder sort( array $fields ) order fields from collection
 * @method array|callable get( int $count = 0 ) get result
 * @method array mapping() get mapping
 * @method mixed find( $id, bool $get_only_source = true ) get first result only
 * @method array|callable paginate( int $document_per_page = 10, int $current_page = 1 ) get documents paginated
 * @package ElastiCute\ElastiCute
 * @author  Payam Jafari/payamjafari.ir
 * @see     http://payamweber.github.io/elasticute
 */
class QueryBuilder
{
	/**
	 * Databse credensials
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
	
	/** @var array $current_group_depth_info */
	protected static array $current_group_depth_info = [
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
		$ref       = new \ReflectionClass( ClassLoader::class );
		$envreader = Dotenv::createImmutable( dirname( $ref->getFileName() ) . '/../../' );
		$envreader = $envreader->safeLoad();

//		$this->db_address = self::getEnv( 'ELCUTE_DB_ADDRESS', '127.0.0.1' );
//		$this->db_port    = self::getEnv( 'ELCUTE_DB_PORT', '27017' );
//		$this->index_name = self::getEnv( 'ELCUTE_DB_NAME', '' );
//		$this->db_user    = self::getEnv( 'ELCUTE_DB_USERNAME', '' );
//		$this->db_pass    = self::getEnv( 'ELCUTE_DB_PASSWORD', '' );

//		$userpass      = $this->db_user ? "{$this->db_user}:{$this->db_pass}@" : '';
		$this->elastic = ClientBuilder::create()->build();
		
		try {
			$this->connected = true;
		} catch ( \Exception $e ) {
			$this->connected = false;
		}
	}
	
	public function __call( $name, $arguments )
	{
		return $this->call( $name, $arguments );
	}
	
	public static function __callStatic( $name, $arguments )
	{
		$self = new static();
		return $self->call( $name, $arguments );
	}
	
	/**
	 * Handle builtin methods as static or non static
	 *
	 * @param $name
	 * @param $args
	 * @todo refactor this in near future :)
	 *
	 * @return $this|array|callable
	 * @throws ElastiCuteException
	 */
	protected function call( $name, $args )
	{
		$available_args = $args;
		$args[ 'arg1' ] = $args[ 'tb' ] = $args[ 'count' ] = $args[ 0 ] ?? '';
		$args[ 'arg2' ] = $args[ 'field1' ] = $args[ 1 ] ?? '';
		$args[ 'arg3' ] = $args[ 'join_operator' ] = $args[ 2 ] ?? '';
		$args[ 'arg4' ] = $args[ 3 ] ?? '';
		
		switch ( $name = strtolower( $name ) ) {
			case 'index':
				return $this->doSelectIndex( $args[ 'arg1' ] );
			case 'aggregate':
				return $this->doAggregate( $args[ 'arg1' ] );
			case 'groupmust':
				return $this->boolianGroup( $args[ 'arg1' ], 'must' );
			case 'groupfilter':
				return $this->boolianGroup( $args[ 'arg1' ], 'filter' );
			case 'groupshould':
				return $this->boolianGroup( $args[ 'arg1' ], 'should' );
			case 'groupmustnot':
				return $this->boolianGroup( $args[ 'arg1' ], 'must_not' );
			case 'wherecontains':
				return $this->doWhere( $args[ 'arg1' ], $args[ 'arg2' ],
					isset( $available_args[ 2 ] ) ? ( $available_args[ 2 ] ? 'match_phrase' : 'match' ) : 'match' );
			case 'whereequal':
				return $this->doWhere( $args[ 'arg1' ], $args[ 'arg2' ], 'term' );
			case 'wherenotequal':
				return $this->boolianGroup( function ( QueryBuilder $builder ) use ( $args ) {
					$builder->doWhere( $args[ 'arg1' ], $args[ 'arg2' ], 'term' );
				}, 'must_not' );
			case 'wherenotcontains':
				return $this->boolianGroup( function ( QueryBuilder $builder ) use ( $args, $available_args ) {
					$builder->doWhere( $args[ 'arg1' ], $args[ 'arg2' ],
						isset( $available_args[ 2 ] ) ? ( $available_args[ 2 ] ? 'match_phrase' : 'match' ) : 'match' );
				}, 'must_not' );
			case 'whereexists':
				return $this->doWhere( 'field', $args[ 'arg1' ], 'exists' );
			case 'wherenotexists':
				return $this->boolianGroup( function ( QueryBuilder $builder ) use ( $args ) {
					$builder->doWhere( 'field', $args[ 'arg1' ], 'exists' );
				}, 'must_not' );
			case 'select':
				return $this->doSelect( $args[ 'arg1' ] ?: [] );
			case 'sort':
				return $this->doSort( $args[ 'arg1' ] ?: [] );
			case 'get':
				return call_user_func_array( [ self::class, 'doGet' ], $available_args );
			case 'paginate':
				return $this->doGet( $args[ 'arg1' ] !== '' ?: 10, true, $args[ 'arg2' ] !== '' ?: 10 );
			case 'find':
				return call_user_func_array( [ self::class, 'doFind' ], $available_args );
			case 'mapping':
				return $this->doMapping();
		}
		
		return $this;
	}
	
	/**
	 * Start the query builder
	 *
	 * @return static
	 */
	public static function query(): self
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
			$this::$current_depth_info[ $current_info_count - 1 ][ 'conditions' ][] = $condition_query;
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
	protected function doSort( array $fields )
	{
		$this->query_sort = $fields;
		
		return $this;
	}
	
	/**
	 * @param array $fields
	 *
	 * @return $this
	 */
	protected function doSelect( array $fields )
	{
		$this->query_select = $fields;
		
		return $this;
	}
	
	/**
	 * @param        $db
	 * @param        $field1
	 * @param        $operator
	 * @param        $field2
	 * @param string $type
	 *
	 * @return $this
	 */
	protected function join( $db, $field1, $operator, $field2, $type = 'inner join' )
	{
		$this->query_join[] = "$type $db on $field1 $operator $field2";
		
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
		$current_info_count  = count( $this::$current_depth_info );
		$is_already_in_group = $this->is_group_where;
		
		$this->is_group_where                             = true;
		$this::$current_depth_info[ $current_info_count ] = [
			'type' => $operator,
			'conditions' => [],
		];
		$filters( $this );
		if ( $is_already_in_group ) {
			$this::$current_depth_info[ $current_info_count - 1 ][ 'conditions' ][] = [
				'bool' => [
					$this::$current_depth_info[ $current_info_count ][ 'type' ] =>
						$this::$current_depth_info[ $current_info_count ][ 'conditions' ],
				],
			];
		} else {
			$this->is_group_where = false;
			
			$this->query_where[][ 'bool' ] = [
				$this::$current_depth_info[ $current_info_count ][ 'type' ] =>
					$this::$current_depth_info[ $current_info_count ][ 'conditions' ],
			];
		}
		
		return $this;
	}
	
	/**
	 * @param      $id
	 * @param bool $get_only_source
	 *
	 * @return mixed
	 * @throws ElastiCuteException
	 */
	protected function doFind( $id, bool $get_only_source = true )
	{
		$this->initializeDatabaseAndCollection();
		
		$method = $get_only_source ? 'getSource' : 'get';
		
		try {
			return $this->elastic->$method( [
				'index' => $this->index_name,
				'id' => $id,
				'_source' => $this->query_select,
			] );
		} catch ( BadRequest400Exception $exception ) {
			$error_message = json_decode( $exception->getMessage(), true );
			
			throw new ElastiCuteException( $error_message[ 'error' ][ 'root_cause' ][ 0 ][ 'reason' ] ?? $exception->getMessage(), 400 );
		}
	}
	
	/**
	 * @param int  $count
	 * @param bool $paginate
	 * @param int  $page_number
	 *
	 * @return array|callable
	 * @throws ElastiCuteException
	 */
	protected function doGet( $count = 10, bool $paginate = false, $page_number = 1 )
	{
		$this->initializeDatabaseAndCollection();
		
		try {
			return $this->elastic->search( [
				'index' => $this->index_name,
				'body' => [
						'query' => [ 'bool' => [ 'must' => $this->query_where ] ] ?: [
							'match_all' => (object)[],
						],
						'sort' => $this->query_sort ?: (object)[],
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
		} catch ( BadRequest400Exception $exception ) {
			$error_message = json_decode( $exception->getMessage(), true );
			
			throw new ElastiCuteException( $error_message[ 'error' ][ 'root_cause' ][ 0 ][ 'reason' ] ?? $exception->getMessage(), 400 );
		}
	}
	
	/**
	 * @param       $name
	 * @param mixed $default
	 *
	 * @return array|false|string|null
	 */
	protected static function getEnv( $name, $default = null )
	{
		return isset( $_ENV[ $name ] ) ? $_ENV[ $name ] : $default;
	}
	
	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	protected function doSelectIndex( string $name )
	{
		$this->index_name = $name;
		return $this;
	}
	
	/**
	 * @throws ElastiCuteException
	 */
	protected function initializeDatabaseAndCollection()
	{
		if ( !$this->connected ) {
			throw new ElastiCuteException( 'Could not connect to database' );
		}
		
		if ( !$this->index_name ) {
			throw new ElastiCuteException( 'Index name has not been set' );
		}
	}
	
	/**
	 * @return array
	 * @throws ElastiCuteException
	 */
	protected function doMapping()
	{
		$this->initializeDatabaseAndCollection();
		
		return $this->elastic->indices()->getMapping( [
			'index' => $this->index_name,
		] );
	}
	
	/**
	 * @param callable $aggregations
	 *
	 * @return $this
	 */
	protected function doAggregate( callable $aggregations )
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