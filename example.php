<?php

use ElastiCute\ElastiCute\Aggregation\AggregationQuery;
use ElastiCute\ElastiCute\QueryBuilder;

require './vendor/autoload.php';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

try {
    $query = QueryBuilder::query()
        ->index('news_comments')
        ->groupMust(function (QueryBuilder $builder) {
            $builder->whereTextContains('text', 'test');
            $builder->whereGreaterThanOrEqual('@timestamp', '2021-12-05');
            $builder->whereRaw([
                'match' => [
                    'text' => 'test'
                ]
            ]);
        })
        ->select(['_id', '@timestamp'])
        ->get();

    QueryBuilder::dieAndDump($query);
} catch (\ElastiCute\ElastiCute\ElastiCuteException $e) {
}


QueryBuilder::dieAndDump( $query );

$query = QueryBuilder::query()
	->index( 'instagram_profiles' )
	->sort( [
		'products.created_on' => [
			'order' => 'desc',
		],
	] )
	->groupShould( function ( QueryBuilder $builder ) {
		$builder->whereTextContains( 'name', 'payam1' );
		$builder->whereEqual( 'name', 'payam2' );
	} )
	->groupMust( function ( QueryBuilder $builder ) {
		$builder->whereTextContains( 'name', 'payam7' );
		$builder->whereEqual( 'name', 'payam8' );
		$builder->groupMust( function ( QueryBuilder $builder ) {
			$builder->whereTextNotContains( 'name', 'payam10' );
			$builder->whereEqual( 'name', 'mamad11' );
		} );
		$builder->whereNotEqual( 'name', 'payam13' );
	} )
	->whereNotEqual( 'currency', 'EUR2' )
	->select( [ 'currency' ] )
	->aggregate( function( AggregationQuery $query ){
//		$query->avgBasic( 'my_group', 'day_of_week_i' );
//		$query->avgAdvanced( 'group2', 'total_quantity', 20 );
		$query->avg()->label('asdfasd')->build();
		$query->avg()->label('sdfsdfdf')->build();
	} )
	->get()
	->map(function( $val ){
		// do some stuff with $val
	});


QueryBuilder::dieAndDump( $query );
