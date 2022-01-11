# ElastiCute - A QueryBuilder for ElasticSearch

By using this builder you can easily make your database queries without writing raw queries.

## Installation

```bash
composer require payamjafari/elasticute
```

## Usage

Just call method "query" in QueryBuilder class and you are ready.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()->index( 'cards' )->get();

// use $cards for your needs
```

### Filter query

For filtering your query you can use several methods such as `where`, `whereNot`, `orWhere`, `...`.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->whereEqual( 'name', 'foo' )
    ->whereNotEqual( 'count', 10 )
    ->get(); // limit documents count by giving a number to "get". Example: get(10);

// use $cards for your needs
```

#### Filter Methods

Name | Description
--- | ---
`where(string $name, $value, string $operator, array $extra)` | Matches values that is not a specified value.
`whereNot(string $name, $value, string $operator, array $extra)` | Matches values that is a specified value.
`whereContains( string $name, $value )` | Matches values that are contains a specified value.
`whereNotContains( string $name, $value )` | Matches values that are not contains a specified value.
`whereEqual( string $name, $value )` | Matches values that are equal to a specified value.
`whereNotEqual( string $name, $value )` | Matches all values that are not equal to a specified value.
`whereExists( string $name )` | Matches documents that have the specified field.
`whereNotExists( string $name )` | Matches documents that dont have the specified field.

### Group Filter

You can set your filters as a group by just adding a closure to "where" method.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->groupShould( function( QueryBuilder $builder ){
        $builder->whereEqual( 'name', 'payam' );
        $builder->whereNotEqual( 'name', 'kourosh' );
        $builder->groupMust( function( QueryBuilder $builder ){
            $builder->whereExists( 'lastname' );
            $builder->whereTextContains( 'name', 'kourosh2' );
        } );
    } )
    ->whereTextNotContains( 'lastname', 'weber' )
    ->get();

// use $cards for your needs
```

#### Group Filter Methods

Name | Description
--- | ---
`groupShould( callable $filters )` | [See Official Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)
`groupMust( callable $filters )` | [See Official Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)
`groupMustNot( callable $filters )` | [See Official Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)
`groupFilter( callable $filters )` | [See Official Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)

### Aggregations

Aggregations are so important and could be used anywhere. So you can use it very easy. Take a look at the example.

```php
<?php

use ElastiCute\ElastiCute\Aggregation\AggregationBuilder;
use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->aggregate( function( AggregationBuilder $query ){
        $query->make('like_count')->avg()->field('like_count');
        $query->make('dislike_count')->avg()->field('dislike_count');
    } )
    ->whereTextNotContains( 'lastname', 'weber' )
    ->get();

// response will include $cards['aggregations']
```

#### Aggregation methods

Name | Description
--- | ---
`avg()` | Aggregate based on 'avg' type
`terms()` | Aggregate based on 'terms' type
`histogram()` | Aggregate based on 'histogram' type
`max()` | Aggregate based on 'max' type
`min()` | Aggregate based on 'min' type

###### **Note**: More aggregations will be added over time :)


### Response

Each Request has a response, then it must be processed.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->get()
    ->toArray();

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->get()
    ->map( function( $value ) {
        // Do some stuff with $value
    } );

// use $cards for your needs
```

#### Response Methods

Name | Description
--- | ---
`toArray()` | Returns as array.
`toJson()` | Returns as json.
`toList()` | Returns as array list (if its a search query).
`map()` | Map through the results(if its a search query)

### Sort / OrderBy

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->sort( [
        'name' => [
            'order' => 'desc'
        ]
    ] )
    ->get();

// use $cards for your needs
```

### Paginate documents

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->paginate( 10, 1 ); // paginate( int $document_per_page = 10, int $current_page = 1 )

// use $cards for your needs
```

### Select specific fields

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->select( [ 'name', 'size' ] )
    ->get();

// use $cards for your needs
```

### Select index at runtime

You can select your index at the query by calling "index" method.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->whereEqual( 'name', 'foo' ) // your filters come here before delete
    ->get();
```

### Find by id

By calling method "find", you can retrieve the document based on id.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$card = QueryBuilder::query()
    ->index( 'cards' )
    ->find( 'wDCoxXUBA0stnoJxvwdR', true ); // find( $id, bool $get_only_source = true )
```

### Get index mapping

By calling method "mapping", you can retrieve the index mapping.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$card = QueryBuilder::query()
    ->index( 'cards' )
    ->mapping();
```

### Pro tip :)

You dont have to call "query" method first. you can directly call index right at the beginning.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$card = QueryBuilder::index( 'cards' )
    ->find( 'wDCoxXUBA0stnoJxvwdR', true ); // find( $id, bool $get_only_source = true )

$cards = QueryBuilder::index( 'cards' )
    ->whereEqual( 'name', 'foo' )
    ->get();
```
