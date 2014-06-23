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
Dba::config( [
    'dsn'  => 'mysql:host=localhost;dbname=name;charset=utf',
    'user' => 'username',
    'pass' => 'password',
] );
```

for reading and writing,

```php
Dba::config( [
    'dsn'  => 'mysql:host=localhost;dbname=name;charset=utf',
    'user' => 'username',
    'pass' => 'password',
    'for'  => 'write',
] );
```

### get connection (i.e. Pdo)

getting the connection.

```php
$pdo = Dba::db();
$pdo2 = Dba::dbWrite();
```

uses AuraPHP's ExtendedPdo and ConnectionLocator as default.


### get named connection

get another connection with different configuration.

```php
Dba::config( 'log', [
    'dsn' => 'mysql',...
] );
// ...
$pdo = Dba::db( 'log' );
```

### transaction

for transaction, use the Pdo (ExtendedPdo)'s transaction method.

```php
Dba::db()->beginTransaction();
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

