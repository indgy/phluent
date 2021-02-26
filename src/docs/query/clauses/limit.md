# Limit

The ``limit()`` limits the number of returned rows.

You may also use the alias `take()`.

Limiting the number of returned rows:
```php
$query->table('movies')->limit(10);
// SELECT * FROM `movies` LIMIT 10
```