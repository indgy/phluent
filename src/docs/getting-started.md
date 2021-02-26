# Getting started

## Installation

Phluent can be used as a composer package or a single file download and included into your project.

### Using Composer

``` bash
$ composer require indgy/phluent
```

### Standalone

Use the standalong Phluent.php file in the dist folder which combines Query, DB and the functions files into one.

```bash
cp dist/Phluent.php /path/to/your/project
```
Then require it where needed in your project:

```php
require("/path/to/your/project/Phluent.php");
```

## Creating a PDO connection

DB requires a [PDO](https://www.php.net/manual/en/pdo.construct.php) connection to your database.

```php
$dsn = 'mysql:host=localhost;port=3306;dbname=database;charset=utf8mb4';
$pdo = new \PDO($dsn, 'username', 'password', [
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    \PDO::ATTR_EMULATE_PREPARES => true,
]);
```

You can now use [DB](db.md) to perform SQL queries on the database:

```php
$db = new DB($pdo);
$db->query("SELECT * FROM `movies` WHERE `title` LIKE '%Muppets%'");

foreach ($db->get() as $movie) {
  echo "{$movie->title} was released in {$movie->year}";
}
```

Read the [Query](query.md) guide for more details on generating SQL.

!!! note
    The PDO guide at [PHP delusions](https://phpdelusions.net/pdo) is a good read.

