<?php

namespace ElastiCute\ElastiCute;

use Composer\Autoload\ClassLoader;
use Dotenv\Dotenv;
use Elasticsearch\ClientBuilder;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\DeleteResult;
use MongoDB\InsertOneResult;
use MongoDB\UpdateResult;

/**
 * Class QueryBuilder
 *
 * @method QueryBuilder db( $name )
 * @method QueryBuilder table( $name )
 * @method QueryBuilder where( string $name, $value = '', string $operator = '$eq' )
 * @method QueryBuilder whereEqual( string $name, $value )
 * @method QueryBuilder whereNot( string $name, $value )
 * @method QueryBuilder whereIn( string $name, array $values )
 * @method QueryBuilder whereNotIn( string $name, array $values )
 * @method QueryBuilder whereGreaterThan( string $name, $value )
 * @method QueryBuilder whereGreaterThanOrEqual( string $name, $value )
 * @method QueryBuilder whereLessThan( string $name, $value )
 * @method QueryBuilder whereLessThanOrEqual( string $name, $value )
 * @method QueryBuilder whereExists( string $name, $value )
 * @method QueryBuilder whereType( string $name, $value )
 * @method QueryBuilder orWhere( string $name, $value = '', string $operator = '$eq' )
 * @method QueryBuilder orWhereEqual( string $name, $value )
 * @method QueryBuilder orWhereNot( string $name, $value )
 * @method QueryBuilder orWhereIn( string $name, array $values )
 * @method QueryBuilder orWhereNotIn( string $name, array $values )
 * @method QueryBuilder orWhereGreaterThan( string $name, $value )
 * @method QueryBuilder orWhereGreaterThanOrEqual( string $name, $value )
 * @method QueryBuilder orWhereLessThan( string $name, $value )
 * @method QueryBuilder orWhereLessThanOrEqual( string $name, $value )
 * @method QueryBuilder orWhereExists( string $name, $value )
 * @method QueryBuilder orWhereType( string $name, $value )
 * @method QueryBuilder select( array $fields ) select fields from collection
 * @method QueryBuilder orderby( array $fields, $order = 'ASC' ) order fields from collection
 * @method array get( int $count = 0 ) get result
 * @method array|object|null first() get first result only
 * @method \MongoDB\InsertOneResult create( array $data ) Insert an Doc into table
 * @method \MongoDB\InsertManyResult createMany( array $data ) Insert Docs into table
 * @method \MongoDB\UpdateResult update( array $data ) Update Docs
 * @method \MongoDB\DeleteResult delete() Delete Docs
 * @package ElastiCute\ElastiCute
 * @author  Payam Jafari/payamjafari.ir
 * @see     http://payamweber.github.io/mongocute
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
	
	/**
	 * @var string $query_groupby
	 */
	protected $query_groupby;
	
	/**
	 * @var array $query_select
	 */
	protected $query_select = [];
	
	/**
	 * @var string $query_table
	 */
	protected string $query_table = '';
	
	/**
	 * @var bool $is_group_where
	 */
	protected bool $is_group_where = false;
	
	/**
	 * @var bool $is_count
	 */
	protected $is_count = false;
	
	protected static $current_depth_info = [
		'type' => '$and',
		'conditions' => [],
	];
	
	/**
	 * @var \Elasticsearch\Client $elastic
	 */
	protected $elastic;
	
	/**
	 * this is allowed operators for queries
	 */
	protected const ALLOWED_OPERATORS = [ '$eq', '$ne', '$gt', '$gte', '$lt', '$lte', '$in', '$nin', '$exists', '$type' ];
	
	/**
	 * Model constructor.
	 */
	public function __construct()
	{
		$ref       = new \ReflectionClass( ClassLoader::class );
		$envreader = Dotenv::createImmutable( dirname( $ref->getFileName() ) . '/../../' );
		$envreader = $envreader->safeLoad();
		
		$this->db_address = self::getEnv( 'ELCUTE_DB_ADDRESS', '127.0.0.1' );
		$this->db_port    = self::getEnv( 'ELCUTE_DB_PORT', '27017' );
		$this->index_name = self::getEnv( 'ELCUTE_DB_NAME', '' );
		$this->db_user    = self::getEnv( 'ELCUTE_DB_USERNAME', '' );
		$this->db_pass    = self::getEnv( 'ELCUTE_DB_PASSWORD', '' );
		
		$userpass      = $this->db_user ? "{$this->db_user}:{$this->db_pass}@" : '';
		$this->elastic = ClientBuilder::create()->build();
		
		try {
			$this->connected = true;
		} catch ( \Exception $e ) {
			$this->connected = false;
		}
	}
	
	public function __call( $name, $arguments )
	{
		return $this->_call( $name, $arguments );
	}
	
	public static function __callStatic( $name, $arguments )
	{
		$self = new static();
		return $self->_call( $name, $arguments );
	}
	
	/**
	 * handle builtin methods as static or non static
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return $this|mixed|Model
	 */
	protected function _call( $name, $args )
	{
		$args[ 'arg1' ] = $args[ 'tb' ] = $args[ 'count' ] = $args[ 0 ] ?? '';
		$args[ 'arg2' ] = $args[ 'field1' ] = $args[ 1 ] ?? '';
		$args[ 'arg3' ] = $args[ 'join_operator' ] = $args[ 2 ] ?? '';
		$args[ 'arg4' ] = $args[ 3 ] ?? '';
		
		switch ( $name = strtolower( $name ) ) {
			case 'index':
				return $this->selectIndex( $args[ 'arg1' ] );
				break;
			case 'table':
				return $this->_table( $args[ 'arg1' ] );
				break;
			case $name == 'orwhere' ? 'orwhere' : 'where':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], $args[ 'arg3' ], $name == 'orwhere' ? '$or' : '$and' );
				break;
			case $name == 'orwhereequal' ? 'orwhereequal' : 'whereequal':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$eq', $name == 'orwhereequal' ? '$or' : '$and' );
				break;
			case $name == 'orwherenot' ? 'orwherenot' : 'wherenot':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$ne', $name == 'orwherenot' ? '$or' : '$and' );
				break;
			case $name == 'orwheregreaterthan' ? 'orwheregreaterthan' : 'wheregreaterthan':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$gt', $name == 'orwheregreaterthan' ? '$or' : '$and' );
				break;
			case $name == 'orwheregreaterthanorequal' ? 'orwheregreaterthanorequal' : 'wheregreaterthanorequal':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$gte', $name == 'orwheregreaterthanorequal' ? '$or' : '$and' );
				break;
			case $name == 'orwherelessthan' ? 'orwherelessthan' : 'wherelessthan':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$lt', $name == 'orwherelessthan' ? '$or' : '$and' );
				break;
			case $name == 'orwherelessthanorequal' ? 'orwherelessthanorequal' : 'wherelessthanorequal':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$lte', $name == 'orwherelessthanorequal' ? '$or' : '$and' );
				break;
			case $name == 'orwherein' ? 'orwherein' : 'wherein':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$in', $name == 'orwherein' ? '$or' : '$and' );
				break;
			case $name == 'orwherenotin' ? 'orwherenotin' : 'wherenotin':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$nin', $name == 'orwherenotin' ? '$or' : '$and' );
				break;
			case $name == 'orwhereexists' ? 'orwhereexists' : 'whereexists':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$exists', $name == 'orwhereexists' ? '$or' : '$and' );
				break;
			case $name == 'orwheretype' ? 'orwheretype' : 'wheretype':
				return $this->_where( $args[ 'arg1' ], $args[ 'arg2' ], '$type', $name == 'orwheretype' ? '$or' : '$and' );
				break;
			case 'select':
				return $this->_select( $args[ 'arg1' ] ?: [] );
				break;
			case 'sort':
				return $this->_sort( $args[ 'arg1' ] ?: [] );
				break;
			case 'create':
				return $this->_insert( $args[ 'arg1' ] );
				break;
			case 'createmany':
				return $this->_insert( $args[ 'arg1' ], true );
				break;
			case 'update':
				return $this->_update( $args[ 'arg1' ] );
				break;
			case 'delete':
				return $this->_delete();
				break;
			case 'get':
				return $this->_get( $args[ 'arg1' ] );
				break;
			case 'mapping':
				return $this->_mapping();
				break;
			case 'count':
				return $this->_count();
				break;
			default:
				return $this;
				break;
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
	 * @param string $operator_or_value
	 * @param string $value
	 * @param string $type
	 *
	 * @return $this
	 */
	protected function _where( $key, $value = '', $operator = '$eq', $type = '$and' )
	{
		$operator = in_array( $operator, self::ALLOWED_OPERATORS ) ? $operator : '$eq';
		
		if ( $key ) {
			$this->add_where_condition( $key, $value, $operator, $type );
		}
		
		return $this;
	}
	
	/**
	 * @param        $key
	 * @param        $value
	 * @param string $operator
	 * @param string $type
	 */
	protected function add_where_condition( $key, $value, $operator = '=', $type = '$and' )
	{
		if ( $this->query_where ) {
			if ( $type == '$or' && $this::$current_depth_info[ 'type' ] == '$and' ) {
				if ( $this->is_group_where ) {
					$this::$current_depth_info[ 'type' ] = '$or';
				} else {
					$this->query_where[ '$or' ] = $this->query_where[ '$and' ];
					unset( $this->query_where[ '$and' ] );
				}
			}
		} else {
			$this->query_where[ $type ] = [];
		}
		// set group where conditions
		if ( is_callable( $key ) ) {
			$is_already_in_group       = $this->is_group_where;
			$this->is_group_where      = true;
			$this::$current_depth_info = [
				'type' => '$and',
				'conditions' => [],
			];
			$key( $this );
			if ( !$is_already_in_group ) {
				$this->is_group_where = false;
			}
			$index                         = array_keys( $this->query_where );
			$index                         = reset( $index );
			$this->query_where[ $index ][] = [
				$this::$current_depth_info[ 'type' ] => $this::$current_depth_info[ 'conditions' ],
			];
		} else {
			$condition_query = [
				$key => [
					$operator => $value,
				],
			];
			
			if ( $this->is_group_where ) {
				$this::$current_depth_info[ 'conditions' ][] = $condition_query;
			} else {
				$index                         = array_keys( $this->query_where );
				$index                         = reset( $index );
				$this->query_where[ $index ][] = $condition_query;
			}
		}
	}
	
	/**
	 * @param array  $fields
	 * @param string $order
	 *
	 * @return $this
	 */
	protected function _sort( array $fields )
	{
		$this->query_sort = $fields;
		
		return $this;
	}
	
	/**
	 * @param $name
	 *
	 * @return $this
	 */
	protected function _select( array $fields )
	{
		$_fields = [];
		foreach ( $fields as $field ) {
			$_fields[ $field ] = 1;
		}
		
		$this->query_select = $_fields;
		
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
	protected function _join( $db, $field1, $operator, $field2, $type = 'inner join' )
	{
		$this->query_join[] = "$type $db on $field1 $operator $field2";
		
		return $this;
	}
	
	/**
	 * @param $name
	 *
	 * @return $this
	 */
	protected function _table( $name )
	{
		if ( $name )
			$this->query_table = $name;
		
		return $this;
	}
	
	/**
	 * @return bool|mixed
	 */
	protected function _first()
	{
		return $this->_get( 1, true );
	}
	
	/**
	 * @param int  $count
	 * @param bool $paginate
	 * @param int  $pagenumber
	 *
	 * @return array|callable
	 * @throws ElastiCuteException
	 */
	protected function _get( $count = 10, bool $paginate = false, $pagenumber = 1 )
	{
		$this->initializeDatabaseAndCollection();

//		var_dump($this->elastic->getSource([
//			'index' => 'kibana_sample_data_ecommerce',
//			'id' => 'wDCoxXUBA0stnoJxvwdR']) );
//		var_dump( $this->query_sort );
//		die();
		return $this->elastic->search( [
			'index' => $this->index_name,
			'body' => [
					'query' => $this->query_where ?: [
						'match_all' => (object)[],
					],
					'sort' => $this->query_sort ?: (object)[],
					'size' => intval( $count ?: -1 ),
				]
				+ ( $paginate ? [
					'from' => $pagenumber * $count,
				] : [] ),
		] );
	}
	
	/**
	 * @return int
	 * @throws ElastiCuteException
	 */
	protected function _count()
	{
		$this->initializeDatabaseAndCollection();
		
		return $this->elastic->countDocuments( $this->query_where );
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
	 * @param array $data
	 * @param bool  $multiple
	 *
	 * @return mixed
	 * @throws ElastiCuteException
	 */
	protected function _insert( array $data, $multiple = false )
	{
		$this->initializeDatabaseAndCollection();
		
		$call = $multiple ? 'insertMany' : 'insertOne';
		return $this->elastic->$call( $data );
	}
	
	/**
	 * @param array $data
	 *
	 * @return UpdateResult
	 * @throws ElastiCuteException
	 */
	protected function _update( array $data ): UpdateResult
	{
		$this->initializeDatabaseAndCollection();
		
		return $this->elastic->updateMany( $this->query_where, [
			'$set' => $data,
		] );
	}
	
	/**
	 * @return DeleteResult
	 * @throws ElastiCuteException
	 */
	protected function _delete(): DeleteResult
	{
		$this->initializeDatabaseAndCollection();
		
		return $this->elastic->deleteMany( $this->query_where );
	}
	
	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	protected function selectIndex( string $name )
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
	
	protected function _mapping()
	{
		$this->initializeDatabaseAndCollection();
		
		return $this->elastic->indices()->getMapping([
			'index' => $this->index_name
		]);
	}
}