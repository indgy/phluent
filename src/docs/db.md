# Using DB

DB extends [Query](query.md) and inherits all the fluent SQL build methods while adding the following methods for interacting with the database via PDO.

The following methods can be used to query the database:

- `raw(String $sql, ?Array $params)` Returns a [PDOStatement]()
- `query(String $sql, ?Array $params)` Returns the same DB instance, then use:
    - `getOne()` Returns a single object representing a result row
    - `get()` Returns an array of objects representing the result row

DB also [changes](db/changes-from-query.md) the way that aggregate methods are used.