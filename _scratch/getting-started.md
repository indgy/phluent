# Get started


<!-- Then instantiate the DB class, or Mapper class.



You can of course use both at any time. The only difference is that the Mapper class is designed to work with your Entities returning a Collection of Entities or a single Entity, whereas the DB class returns arrays of objects, or a single object in the case of getOne(). -->

## Bootstrapping
You'll need to require() the classes before you use them, see below for an example bootstrap which includes all the classes in the necessary order, copy this and amend the paths to suit your project.

### Bootstrapping Query &amp; DB only
Use the following to bootstrap Phluent if you only need to work with arrays and plain objects.
```php
<?php
$path = '/Users/jo/my-project/pico/db';

require("$path/functions.php");
require("$path/Query.php");
require("$path/DB.php");
```

## Creating a PDO connection

DB requires a PDO connection to your database, check the [PHP docs](https://www.php.net/manual/en/pdo.construct.php) for more information. The PDO guide at [PHP delusions](https://phpdelusions.net/pdo) may also be useful reading.

```php
$dsn = 'mysql:host=localhost;port=3306;dbname=database;charset=utf8mb4';
$pdo = new \PDO($dsn, 'username', 'password', [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    \PDO::ATTR_EMULATE_PREPARES => true,
]);
```

## Using DB to return objects and arrays.
[DB](/DB) inherits all the fluent methods from [Query](/query) and actually operates on the database, the return type depends on the method called. 

* ``get()`` Returns an array of objects representing rows, or an empty array if nothing found.
* ``getOne()`` Returns an object representing a row, or null if not found.

```php
// now create your DB instance 
$db = new DB($pdo);

// The getOne() method returns a single object, or null if not found
$object = $db->from('movies')->where('title', '1984')->getOne();

// The get() method returns an array of objects, or an empty array if none found
$array_of_objects = $db->from('movies')->where('title', 'like', 'The%')->get();
```

### Next steps
Read more about creating SQL statements using [Query](query.md) and executing them with the  [DB](db.md) class.

