<?php

namespace ElastiCute\ElastiCute;

/**
 * Trait ElastiCuteFilters
 *
 * @package ElastiCute\ElastiCute
 */
trait ElastiCuteFilters
{
	/**
	 * @param callable $filters
	 *
	 * @return $this
	 */
	public function groupShould( callable $filters )
	{
		return $this->boolianGroup( $filters, 'should' );
	}
	
	/**
	 * @param callable $filters
	 *
	 * @return $this
	 */
	public function groupMust( callable $filters )
	{
		return $this->boolianGroup( $filters, 'must' );
	}
	
	/**
	 * @param callable $filters
	 *
	 * @return $this
	 */
	public function groupMustNot( callable $filters )
	{
		return $this->boolianGroup( $filters, 'must_not' );
	}
	
	/**
	 * @param callable $filters
	 *
	 * @return $this
	 */
	public function groupFilter( callable $filters )
	{
		return $this->boolianGroup( $filters, 'filter' );
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @param bool   $match_phrase
	 *
	 * @return $this
	 */
	public function whereContains( string $name, $value = '', bool $match_phrase = false )
	{
		return $this->doWhere( $name, $value, $match_phrase ? 'match_phrase' : 'match' );
	}
	
	/**
	 * @param string $name
	 * @param string $value
	 * @param bool   $match_phrase
	 *
	 * @return $this
	 */
	public function whereNotContains( string $name, $value = '', bool $match_phrase = false )
	{
		return $this->groupMustNot( function ( QueryBuilder $builder ) use ( $name, $value, $match_phrase ) {
			$builder->doWhere( $name, $value, $match_phrase ? 'match_phrase' : 'match' );
		} );
	}
	
	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return $this
	 */
	public function whereEqual( string $name, $value )
	{
		return $this->doWhere( $name, $value, 'term' );
	}
	
	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return $this
	 */
	public function whereNotEqual( string $name, $value )
	{
		return $this->groupMustNot( function ( QueryBuilder $builder ) use ( $name, $value ) {
			$builder->doWhere( $name, $value, 'term' );
		} );
	}
	
	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function whereExists( string $name )
	{
		return $this->doWhere( 'field', $name, 'exists' );
	}
	
	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function whereNotExists( string $name )
	{
		return $this->groupMustNot( function ( QueryBuilder $builder ) use ( $name ) {
			$builder->doWhere( 'field', $name, 'exists' );
		} );
	}
}