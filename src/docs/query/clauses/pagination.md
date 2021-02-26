# Pagination

There are two additional methods provided to support pagination.

## Paginate

A shortcut to the offset and limit clauses:

```php
$query->table('movies')->paginate($skip=110, $take=10);
// SELECT * FROM `movies` LIMIT 10 OFFSET 110
```

<!-- ## Scroll

A shortcut for handling continuous scrolling:

```php
$query->table('movies')->paginate($skip=110, $take=10);
// SELECT * FROM `movies` LIMIT 10 OFFSET 110
``` -->
