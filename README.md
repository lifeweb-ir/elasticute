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

## Environment config

You can set your configuration in your project root folder by creating a file named `".env"`
```dotenv
ELCUTE_DB_ADDRESS=127.0.0.1
ELCUTE_DB_PORT=27017
ELCUTE_DB_NAME=mytestdb
ELCUTE_DB_USERNAME=
ELCUTE_DB_PASSWORD=
```

### Filter query

For filtering your query you can use several methods such as `where`, `whereNot`, `orWhere`, `...`.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->whereEqual( 'name', 'foo' )
    ->whereGreaterThan( 'count', 10 )
    ->get(); // limit documents count by giving a number to "get". Example: get(10);

// use $cards for your needs
```

### Filter Methods

Name | Description
--- | ---
`whereEqual( string $name, $value )` | Matches values that are equal to a specified value.
`whereNot( string $name, $value )` | Matches all values that are not equal to a specified value.
`whereIn( string $name, array $values )` | Matches any of the values specified in an array.
`whereNotIn( string $name, array $value )` | Matches none of the values specified in an array.
`whereGreaterThan( string $name, $value )` | Matches values that are greater than a specified value.
`whereGreaterThanOrEqual( string $name, $value )` | Matches values that are greater than or equal to a specified value.
`whereLessThan( string $name, $value )` | Matches values that are less than a specified value.
`whereLessThanOrEqual( string $name, $value )` | Matches values that are less than or equal to a specified value.
`whereExists( string $name, $value )` | Matches documents that have the specified field.
`whereType( string $name, $value )` | Selects documents if a field is of the specified type.

#### Note: All of these can be used with prefix `'or'` and also can be called statically from class.

### Group Filter

You can set your filters as a group by just adding a closure to "where" method.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->where( function( QueryBuilder $builder ){
        $builder->whereIn( 'name', [ 'foo', 'bar' ] );
        $builder->orWhereIn( 'name', [ 'foo2', 'bar2' ] );
    } )
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

### Create/CreateMany

You can create a document by using "create" method or multiple by using "createMany" method.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$card = QueryBuilder::query()
    ->index( 'cards' )
    ->create( [ 'name' => 'foo', 'size' => 'medium' ] );
$cards = QueryBuilder::query()
    ->index( 'cards' )
    ->createMany( [
        [ 'name' => 'foo', 'size' => 'medium' ],
        [ 'name' => 'foo2', 'size' => 'large' ],
    ] );
```

### Update

You can update documents by using "update" method.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$update = QueryBuilder::query()
    ->index( 'cards' )
    ->whereEqual( 'name', 'foo' ) // your filters come here before update
    ->update( [ 'name' => 'foo2' ] );
```

### Delete

You can delete documents by using "delete" method.

```php
<?php

use ElastiCute\ElastiCute\QueryBuilder;

$update = QueryBuilder::query()
    ->index( 'cards' )
    ->whereEqual( 'name', 'foo' ) // your filters come here before delete
    ->delete();
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