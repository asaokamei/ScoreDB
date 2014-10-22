ScoreDB
=======

A simple Database Access Manager and Data Access Object class.

This package provides a quick access for

*   ExtendedPdo and ConnectionPools from AuraPHP/Sql package,
*   slick query composition using ScoreSql, and
*   a simple Dao (Database Access Object).


### license

MIT License


Sample Usage
------------

### configuration

Uses static ```DB``` class as a gateway (aka facade) to database
manager (```DbAccess``` class).

Use ```DB::config()``` method to configure the db connection.

```php
DB::config( [
    'dsn'  => 'mysql:host=localhost;dbname=name;charset=utf',
    'user' => 'username',
    'pass' => 'password',
    'option' => [],
    'attribute' => []
] );
```

Please refer to Aura/Sql components for details of the each
configuration.

Specify ```for``` in the configuration to connect to different
databases for reading and writing.

```php
DB::config( [
    'dsn'  => '...',
] );
DB::config( [
    'dsn'  => '...',
    'for'  => 'write',
] );
```

### get connection (i.e. Pdo)

getting the Pdo object for db connection. The ```ExtendedPdo```
 in Aura/Sql is returned.

```php
$pdo = DB::connect();
$pdo2 = DB::connectWrite();
```

returns the connection for reading if write connection is not set.


### get named connection

Configure different database connection using names.

```php
DB::config( 'log', [
    'dsn' => 'mysql',...
] );
// then get PDO as:
$pdo = DB::connect( 'log' );
```

Querying Database
-----------------

Use ```DB::query()``` to get a query object to access database.
 Please refer to ScoreSql to find out how to manipulate the query.

```php
$result = DB::query( 'myTable' )
    ->connect( 'conn' )
    ->where( DB::given( 'status' )->is( 5 ) )
    ->select();
```

The ```connect()``` maybe omitted if the default connection is used.


### EntityObject

As default, the Dao object returns the found database
record as ```EntityObject``` class. This class has a
reference to the Dao object to provide many useful
functions while keeping the code base to a minimum size.

some functions are:

*   mutating values to an object (or back).
*   accessing data as a property, or as array.
*   finding the modified data.
*   get the primary key.
*   get the relation objects.


### ActiveRecord

Dao can return object as ActiveRecord class, as

```php
class YourDao extends Dao
{
    protected $fetch_class = 'WScore\ScoreDB\Entity\ActiveRecord';
}
```

The ActiveRecord class will provide more functions
added to the EntityObject described above, such as,

*   save to database.
*   delete itself from database.
*   immunize the access to database.


Data Access Object
------------------

### sample dao class

Extend ```Dao``` class.

```php
/**
 * @method User status( $status=1 )
 */
class User extends Dao
{
    protected $table = 'dao_user';
    protected $keyName = 'user_id';
    protected $timeStamps = [
        'created_at' => [
            'created_at',
            'open_date' => 'Y-m-d'
        ],
        'updated_at' => [
            'updated_at'
        ],
    ];

    /**
     * @param int $status
     */
    public function scopeActive() {
        $this->where( $this->status->is( 1 ) );
    }
}
```


##### table name and primary key

specify table name as ```class::$table```, and primary key
name as ```class::$keyName```.

If these are not set, class name is used as table name, and
  ```tableName_id``` is used as key name.


##### timestamps

Use ```class::$timeStamps``` to indicate stamps:
 ```created_at``` for at the creation of data,
 and ```updated_at``` for updating and creation time.

Specify the date format if different


##### using DaoTrait

alternatively, use DaoTrait to create dao object using
other query class. as such,

```php
class Other extends OtherQuery
{
    user DaoTrait;
}
```



### accessing database

Use $dao object just like a Query object in WScore.SqlBuilder.

##### selecting:

```php
// list all users with status=1.
$found = $user->where(
    $user->status->is(1)
)->select();
```

or use $dao object as an iterator.

```php
$users = User::forge();
foreach( $users as $user ) {
    echo $user->name;
}
```

##### updating:

```php
// update active people to status=2.
$user->active()->update('status'=>2);
```

or,

```php
$user->status = 2;
$user->active()->update();
```


##### inserting:

```php
$user->insert( [ 'name' => 'bob', 'status'=>0 ] );
```

or,

```php
$user->name = 'bob';
$user->status = 0;
$user->insert();
```


Scopes and Events in Dao
------------------------

### scopes

Scopes are functions starting with ```scope```. In the scope
function, influence the query to get what is desired.

```php
$user = User::forge();
// use $dao as iterator.
foreach( $user->active() as $applied ) {
    echo $applied->name;
}
```

### hooks

Hook methods starts with ```on```, event name,
and end with ```Hook```.

```php
class User extends
    public function on{EventName}Hook( $data ) {
    }
}
```


### filters

Filter methods starts with ```on```, event name,
and end with ```Filter```.

Filters are for modifying the input or output;
 make very sure that filters return what is given
 (or modified value) or nothing will happen.

```php
class User extends
    public function on{EventName}Filter( $data ) {
        return $data;
    }
}
```


### hook objects

The Dao class may become too large with lots of scopes
 and event hooks. To simplify the dao class, these
 methods can be transferred to another object (hook object)
 using ```setHook()``` method, such as:

```php
// $hookObject has the events and scopes.
$user->setHook( $hookObject );
```

### available events

whenever accessing database, start with ```~ing```,
and followed by ```~ed```.

*   selecting, selected,
*   loading, loaded,
*   counting, counted,
*   inserting, inserted,
*   updating, updated,
*   deleting, deleted,

hidden (or already used) events:

*   createStamp, updateStamp:

for adding timestamps to data when inserting or updating data.


Relation
--------

ScoreDB provides simple relationship classes as,

*   HasOne,
*   HasMany, and
*   HasJoin.

### Using Relation

In your Dao class, create methods like'

```php
class YourDao extends Dao
{
    public function getTargetsRelation() {
        return Relation::HasOne( $this, 'TargetDaoName' );
    }
}
$dao     = new YourDao();
$entity  = $dao->find($id);

// get target record using HasOne relation.
$targets = $dao->getTargetsRelation()->entity( $entity )->get();

// or use EntityObject's magic method.
$targets = $entity->targets->get();
```

to set new relation,

```php
$entity->targets->link( $targetEntity );
```


WISHES WISHES WISHES
====================


### transaction

for transaction, use the Pdo (ExtendedPdo)'s transaction method.

```php
DB::db()->transaction( function() {
    // do database access.
} );
```

