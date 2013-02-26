WScore.DbAccess
===============

Database access and query builder.

Connecting To Database via Pdo
------------------------------

construct objects using scripts.

```php
require_once( __DIR__ . '/path/to/scripts/require.php' );
$query = include( __DIR__ . '/path/to/scripts/query.php' );
$query->connect( 'dsn=mysql:dbname=test_WScore username=admin password=admin' );
```


Query Usage Example
-------------------

###Getting Data

finding data whose name is 'Bob' from 'table'.

```php
$found = $query->table( 'table' )->name->eq( 'Bob' )->select();
```

finding data whose name contain (like) 'Bob' from 'table'.

```php
$found = $query->table( 'table' )->name->contain( 'Bob' )->select();
```

finding maximum of 10 data whose name contain 'Bob', age is greater than 20, from 'table'.

```php
$found = $query->table( 'table' )->name->contain( 'Bob' )->age->gt( 20 )->limit( 10 )->select();
```

###Inserting Data

insert data into table and get the last ID.

```php
$id = $query->table( 'table' )->insert( array( 'name'=>'Alan Kay' ) )->lastId();
```

###Update data

update the data whose name is 'Bob' to 'Bob Dylan'.

```php
$query->table( 'table' )->name->eq( 'Bob' )->update( array( 'name' => 'Bob Dylan' ) );
```

###Deleting data

delete the data whose 'table_id' is equals to '10'.
or, alternatively, set primary key (id) of 'table' as 'table_id' in table method, then delete the data whose primary key is '10'.

```php
$query->table( 'table' )->table_id->eq( 10 )->delete();
$query->table( 'table', 'table_id' )->id( 10 )->delete();
```

DbAccess
--------



Classes and Objects
-------------------

Query
: query builder for database access.

QueryObject
: an object representation of a query.
: build by Query and passed to DbAccess object.

DbAccess
: database access object using \Pdo.

SqlBuilder
: converts a QueryObject to SQL statement.
: also construct prepared data or quoted data based on the setting.

DbConnect
: \Pdo constructor.
