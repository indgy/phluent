# Offset

The ``offset()`` method shifts the start position of the returned results. 

You may also use the alias `skip()`.


!!! important
    The ``limit()`` method must be called when using ``offset()``.

Limit the number of returned rows with an offset for pagination:
```php
$query->table('movies')->limit(10)->offset(40);
// SELECT * FROM `movies` LIMIT 10 OFFSET 40
```

