# MongoCute - A QueryBuilder for MongoDB

By using this builder you can easily make your database queries without writing raw queries.

## Installation

```bash
composer require payamjafari/mongocute
```

## Usage

Just call method "query" in QueryBuilder class and you are ready.

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$cards = QueryBuilder::query()->table( 'cards' )->get();

// use $cards for your needs
```

## Environment config

You can set your configuration in your project root folder by creating a file named `".env"`
```dotenv
MGCUTE_DB_ADDRESS=127.0.0.1
MGCUTE_DB_PORT=27017
MGCUTE_DB_NAME=mytestdb
MGCUTE_DB_USERNAME=
MGCUTE_DB_PASSWORD=
```

### Filter query

For filtering your query you can use several methods such as `where`, `whereNot`, `orWhere`, `...`.

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->table( 'cards' )
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

use MongoCute\MongoCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->table( 'cards' )
    ->where( function( QueryBuilder $builder ){
        $builder->whereIn( 'name', [ 'foo', 'bar' ] );
        $builder->orWhereIn( 'name', [ 'foo2', 'bar2' ] );
    } )
    ->get();

// use $cards for your needs
```

### OrderBy

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->table( 'cards' )
    ->orderby( [ 'name', 'size' ], 'DESC' )
    ->get();

// use $cards for your needs
```

### Select specific fields

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->table( 'cards' )
    ->select( [ 'name', 'size' ] )
    ->get();

// use $cards for your needs
```

### Create/CreateMany

You can create a document by using "create" method or multiple by using "createMany" method.

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$card = QueryBuilder::query()
    ->table( 'cards' )
    ->create( [ 'name' => 'foo', 'size' => 'medium' ] );
$cards = QueryBuilder::query()
    ->table( 'cards' )
    ->createMany( [
        [ 'name' => 'foo', 'size' => 'medium' ],
        [ 'name' => 'foo2', 'size' => 'large' ],
    ] );
```

### Update

You can update documents by using "update" method.

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$update = QueryBuilder::query()
    ->table( 'cards' )
    ->whereEqual( 'name', 'foo' ) // your filters come here before update
    ->update( [ 'name' => 'foo2' ] );
```

### Delete

You can delete documents by using "delete" method.

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$update = QueryBuilder::query()
    ->table( 'cards' )
    ->whereEqual( 'name', 'foo' ) // your filters come here before delete
    ->delete();
```

### Select database at runtime

You can select your database at the query by calling "db" method.

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$cards = QueryBuilder::query()
    ->db( 'mytestdb' )
    ->table( 'cards' )
    ->whereEqual( 'name', 'foo' ) // your filters come here before delete
    ->get();
```

### Get first result

By calling method "first" at the last of your query, you can retrieve the first document taken.

```php
<?php

use MongoCute\MongoCute\QueryBuilder;

$card = QueryBuilder::query()
    ->db( 'mytestdb' )
    ->table( 'cards' )
    ->whereEqual( 'name', 'foo' ) // your filters come here before "first"
    ->first(); // retrieves the first document based on filters
```