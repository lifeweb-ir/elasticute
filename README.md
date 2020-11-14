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

### Filter Methods

Name | Description
--- | ---
`whereContains( string $name, $value )` | Matches values that are contains a specified value.
`whereNotContains( string $name, $value )` | Matches values that are not contains a specified value.
`whereEqual( string $name, $value )` | Matches values that are equal to a specified value.
`whereNotEqual( string $name, $value )` | Matches all values that are not equal to a specified value.
`whereExists( string $name, $value )` | Matches documents that have the specified field.
`whereNotExists( string $name, $value )` | Matches documents that dont have the specified field.

### Group Filter Methods

Name | Description
--- | ---
`groupShould( callable $filters )` | [See Official Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)
`groupMust( callable $filters )` | [See Official Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)
`groupMustNot( callable $filters )` | [See Official Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)
`groupFilter( callable $filters )` | [See Official Documentation](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html)

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
            $builder->whereContains( 'name', 'kourosh2' );
        } );
    } )
    ->whereNotContains( 'lastname', 'weber' )
    ->get();

// use $cards for your needs
```

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

You dont have to call "query" method at the first. you can directly call filters right at the beginning.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$card = QueryBuilder::index( 'cards' )
    ->find( 'wDCoxXUBA0stnoJxvwdR', true ); // find( $id, bool $get_only_source = true )

$cards = QueryBuilder::whereEqual( 'name', 'foo' )
    ->index( 'cards' )
    ->get();
```