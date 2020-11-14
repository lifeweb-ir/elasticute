<?php

use MongoCute\MongoCute\QueryBuilder;

require './vendor/autoload.php';

$query = QueryBuilder::query()
	->table( 'books' )
	->whereEqual( 'lastname', 'namjoo' )
	->whereType( 'lastname', 'string' )
	->Where( function ( QueryBuilder $query ) {
		$query->where( 'name', 'mohsen' );
	} )
	->select( [ 'name' ] )
	->orderby( [ 'name' ], 'asc' )
	->get();

QueryBuilder::query()->table( 'books' )->createMany( [
	[ 'name' => 'mohsen', 'lastname' => 'namjoo' ],
	[ 'name' => 'mohsen2', 'lastname' => 'namjoo2' ],
] );

var_dump( $query );