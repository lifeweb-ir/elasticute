<?php

namespace ElastiCute\ElastiCute;

interface QueryBuilderInterface
{
	/**
	 * @param $name
	 *
	 * @return QueryBuilder
	 */
	public function db( $name ): QueryBuilder;

	/**
	 * @param $name
	 *
	 * @return QueryBuilder
	 */
	public function table( $name ): QueryBuilder;

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $operator
	 *
	 * @return mixed
	 */
	public function where( string $name, $value = '', string $operator = '$eq' ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function whereEqual( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function whereNot( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param array  $values
	 *
	 * @return QueryBuilder
	 */
	public function whereIn( string $name, array $values ): QueryBuilder;

	/**
	 * @param string $name
	 * @param array  $values
	 *
	 * @return QueryBuilder
	 */
	public function whereNotIn( string $name, array $values ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function whereGreaterThan( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function whereGreaterThanOrEqual( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function whereLessThan( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function whereLessThanOrEqual( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function whereExists( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function whereType( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param string $value
	 * @param string $operator
	 *
	 * @return QueryBuilder
	 */
	public function orWhere( string $name, $value = '', string $operator = '$eq' ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function orWhereEqual( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function orWhereNot( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param array  $values
	 *
	 * @return QueryBuilder
	 */
	public function orWhereIn( string $name, array $values ): QueryBuilder;

	/**
	 * @param string $name
	 * @param array  $values
	 *
	 * @return QueryBuilder
	 */
	public function orWhereNotIn( string $name, array $values ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function orWhereGreaterThan( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function orWhereGreaterThanOrEqual( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function orWhereLessThan( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function orWhereLessThanOrEqual( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function orWhereExists( string $name, $value ): QueryBuilder;

	/**
	 * @param string $name
	 * @param        $value
	 *
	 * @return QueryBuilder
	 */
	public function orWhereType( string $name, $value ): QueryBuilder;

	/**
	 * @param array $fields
	 *
	 * @return QueryBuilder
	 */
	public function select( array $fields ): QueryBuilder;

	/**
	 * @param array  $fields
	 * @param string $order
	 *
	 * @return QueryBuilder
	 */
	public function orderby( array $fields, $order = 'ASC' ): QueryBuilder;

	/**
	 * @param int $count
	 *
	 * @return array
	 */
	public function get( int $count = 0 ): array;

	/**
	 * @return mixed
	 */
	public function first();

	/**
	 * @param array $data
	 *
	 * @return InsertOneResult
	 */
	public function create( array $data ): InsertOneResult;

	/**
	 * @param array $data
	 *
	 * @return InsertManyResult
	 */
	public function createMany( array $data ): InsertManyResult;

	/**
	 * @param array $data
	 *
	 * @return UpdateResult
	 */
	public function update( array $data ): UpdateResult;

	/**
	 * @return DeleteResult
	 */
	public function delete(): DeleteResult;
}