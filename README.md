WScore.DbAccess
===============

A simple SQL query builder,

and a database connection manager for AuraPHP/Sql component.



WISHES WISHES WISHES
====================

Database Access
---------------

### configuration

configure the database connection. .

```php
DbAccess::config( [
    'dsn'  => 'mysql:host=localhost;dbname=name;charset=utf',
    'user' => 'username',
    'pass' => 'password',
] );
```

for reading and writing,

```php
DbAccess::config( [
    'dsn'  => 'mysql:host=localhost;dbname=name;charset=utf',
    'user' => 'username',
    'pass' => 'password',
    'for'  => 'write',
] );
```

### get connection (i.e. Pdo)

getting the connection.

```php
$pdo = DbAccess::connect();
$pdo2 = DbAccess::connectWrite();
```

uses AuraPHP's ExtendedPdo and ConnectionLocator as default.


### get named connection

get another connection with different configuration.

```php
DbAccess::config( 'log', [
    'dsn' => 'mysql',...
] );
// ...
$pdo = DbAccess::connect( 'log' );
```

### transaction

for transaction, use the Pdo (ExtendedPdo)'s transaction method.

```php
Dba::connect()->beginTransaction();
```

Query
-----

please refer to the WScore.SqlBuilder's Query object.
the API's are the same, but this Query connects to the
database and performs the query, then returns the result.

### getting query

```php
$query = Dba::query( 'myTable', 'my_key', 'aliased' )
```

get query connecting to another db.

```php
$query = Dba::query( 'myTable', 'my_key' )->connect('log');
```

### simple select statement

```php
$found = $query
    ->table('myTable')
    ->column('col1', 'col2')
    ->where(
        $query->status->eq('1')
    )
    ->select();
```

you alternatively use it in for loops.

```php
$query->table('myTable')->where( $query->status->eq('1') );
foreach( $query as $data ) {
    var_dump( $data );
}
```


### simple insert statement

```php
$query
    ->table('myTable')
    ->insert( [ 'col1' => 'val1', 'col2'=>'val2' ] );
```

### simple update statement

```php
$query
    ->table('myTable')
    ->where(
        $query->name->like('bob')->or()->status->eq('1')
    )
    ->update( [
        'date' => Query::raw('NOW()'),
        'col2'=>'val2'
    ] );
```





OLD OLD OLD
===========


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
$found = $query->table( 'table' )->name->eq( 'Bob' )->select()->fetchAll();
```

finding data whose name contain (like) 'Bob' from 'table'.

```php
$found = $query->table( 'table' )->name->contain( 'Bob' )->select()->fetchAll();
```

finding maximum of 10 data whose name contain 'Bob', age is greater than 20, from 'table'.

```php
$found = $query->table( 'table' )->name->contain( 'Bob' )->age->gt( 20 )->limit( 10 )->select()->fetchAll();
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
