<?php

use ElastiCute\ElastiCute\Aggregation\AggregationBuilder;
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
        ->aggregations( function( AggregationBuilder $query ){
            $query->make('like_count')->avg()->field('like_count');
            $query->make('dislike_count')->avg()->field('dislike_count');
            $query->make('dislike_count_term')->terms()->field('dislike_count')->additionalOptions(['size' => 3]);
        } )
        ->select(['_id', '@timestamp', 'text'])
        ->get()
        ->getAggregations();

    QueryBuilder::dieAndDump($query);
} catch (\ElastiCute\ElastiCute\ElastiCuteException $e) {
    QueryBuilder::dieAndDump($e);
}


QueryBuilder::dieAndDump( $query );

try {
    $query = QueryBuilder::query()
        ->index('instagram_profiles')
        ->sort([
            'products.created_on' => [
                'order' => 'desc',
            ],
        ])
        ->groupShould(function (QueryBuilder $builder) {
            $builder->whereTextContains('name', 'payam1');
            $builder->whereEqual('name', 'payam2');
        })
        ->groupMust(function (QueryBuilder $builder) {
            $builder->whereTextContains('name', 'payam7');
            $builder->whereEqual('name', 'payam8');
            $builder->groupMust(function (QueryBuilder $builder) {
                $builder->whereTextNotContains('name', 'payam10');
                $builder->whereEqual('name', 'mamad11');
            });
            $builder->whereNotEqual('name', 'payam13');
        })
        ->whereNotEqual('currency', 'EUR2')
        ->select(['currency'])
        ->aggregations(function (AggregationBuilder $query) {
            $query->make('like_count')->avg()->field('like_count');
            $query->make('dislike_count')->avg()->field('dislike_count');
        })
        ->get()
        ->map(function ($val) {
            // do some stuff with $val
        });
} catch (\ElastiCute\ElastiCute\ElastiCuteException $e) {
}


QueryBuilder::dieAndDump( $query );
