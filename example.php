<?php

use ElastiCute\ElastiCute\QueryBuilder;

require './vendor/autoload.php';

$query = QueryBuilder::query()
	->index( 'kibana_sample_data_ecommerce' )
	->sort( [
		'products.created_on' => [
			'order' => 'desc'
		]
	] )
	->get();

var_dump( $query );