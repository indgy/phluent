# Performing queries
There are two methods that allow the passing of raw SQL statements and parameters, `query()` and `raw()`. Both expect the SQL statement as the first parameter and an array of values as the second.

### Using query()
The ``query()`` method tracks the PDOStatment internally allowing you to continue using the DB methods.
```php
// query(String $sql, Array $params) : DB

$db->query('SELECT * FROM `movies` WHERE `year`=? AND `rating`>?', [2020, 8]);
echo $db->getRowCount();
```

### Using raw()
The ``raw()`` method returns the PDOStatement for you to manage.
```php
// raw(String $sql, Array $params) : PDOStatement

$stmt = $db->raw('SELECT * FROM `movies` WHERE `year`=? AND `rating`>?', [2020, 8]);
foreach ($stmt->fetchAll() as $row) {
    echo $row->title;
}
```

!!! warning
    When using `query()` or `raw()` directly any passed parameters will be escaped by PDO as expected but it is up to you to ensure that your SQL is safe to execute.

!!! note
    All your parameter data is passed to PDO to be escaped as expected, however all references to tables or columns are also sanitised and only specific characters are allowed, these are a-z, 0-9 and the underscore. If you require other characters you may use the ``addReferenceChars()`` method to add more.