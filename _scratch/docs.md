## The Query &amp; DB classes
Query is a fluent SQL statement builder, it's purpose is to create correct SQL statements and track any required parameters. Interacting with the database is the responsibility of the DB class which executes SQL statements and manages the returning of rows and other information such as number of rows affected and the last insert id's.

```php
$query = new \Phluent\Query();
$query->table('movies')->where('year', 2020)->orderBy('rating', 'desc')->limit(10);

// assuming you have set your PDO connection in $pdo
$db = new Phluent\DB($pdo);
$db->query($query->getSql(), $query->getParams());

foreach ($db->get() as $movie) {
    echo "$movie->rating: $movie->title";
}
```
You may also use the shorthand syntax which uses the namespaced function ``query($table_name)``:
```php
$query = query('movies')->where('year', 2020)->orderBy('rating', 'desc')->limit(10);
```

The DB class inherits from Query so you can also write the above example without Query:
```php
// assuming you have set your PDO connection in $pdo
$db = new Phluent\DB($pdo);
$movies = $db->table('movies')->where('year', 2020)->orderBy('rating', 'desc')->limit(10)->get();
foreach ($movies as $movie) {
    echo "$movie->rating: $movie->title";
}
```

<!-- Instead all interaction is performed through [DB](/classes/DB) which will return arrays of rows or objects representing table rows. Or [Mapper](/classes/Mapper) which will return [Collections](/classes/Collections) of [Entities](/classes/Entities), or single Entities.

Phluent consists of the following main classes:

* Query - A standalone SQL statement builder, it does not have any requirements
* DB - Inheriting from Query it adds methods to interact with a database and requires a PDO connection

Using Query you can create the majority of SQL statements using a fluent syntax. Using DB you can execute any SQL query. Plus DB offers the expected helper methods: for basic selecting use the get() and getOne() methods, use insert() or update() to insert or update rows and delete() to remove rows.

However to handle Relationships and give you more control over the query results you can use the following classes:

* Mapper - Inheriting from DB (and therefore Query) mapper requires a PDO connection and an Entity
* Entity - A very simple class containing methods to handle Collections and Relationships
* Collection - An iterable set of query results returned by Mapper
* Relationship - A  -->





