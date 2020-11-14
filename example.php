<?php

use ElastiCute\ElastiCute\QueryBuilder;

require './vendor/autoload.php';

$query = QueryBuilder::query()
	->index( 'kibana_sample_data_ecommerce' )
	->sort( [
		'products.created_on' => [
			'order' => 'desc',
		],
	] )
	->groupShould( function ( QueryBuilder $builder ) {
		$builder->whereContains( 'name', 'payam1' );
		$builder->whereEqual( 'name', 'payam2' );
	} )
	->groupMust( function ( QueryBuilder $builder ) {
		$builder->whereContains( 'name', 'payam7' );
		$builder->whereEqual( 'name', 'payam8' );
		$builder->groupMust( function ( QueryBuilder $builder ) {
			$builder->whereNotContains( 'name', 'payam10' );
			$builder->whereEqual( 'name', 'mamad11' );
		} );
		$builder->whereNotEqual( 'name', 'payam13' );
	} )
	->whereNotEqual( 'currency', 'EUR' )
	->select( [ 'currency' ] )
	->mapping();


echo "<pre>";
var_dump( $query );