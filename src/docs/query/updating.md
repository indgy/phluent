# Updating
The ``update()`` method method generates SQL to update rows in a table, it can be combined with any of the ``where()`` methods, the ``orderBy()`` and ``limit()`` methods.

!!! Important
    The ``update()`` method should be called last.

Pass an associative array representing the columns and values to be updated:
```php
$query->table('movies')
    ->update([
        'archived' => 1
    ]);
// UPDATE `movies` SET `movies`.`archived`='1'
```
You may also use a ``where()`` clause:
```php
$query->table('movies')
    ->where('year', '<', 1984)
    ->update([
        'archived' => 1
    ]);
// UPDATE `movies` SET `movies`.`archived`='1' WHERE `movies`.`year`<'1984'
```
And the ``limit()`` clause:
```php
$query->table('movies')
    ->where('year', '<', 1984)
    ->update([
        'archived' => 1
    ])
    ->limit(10);
// UPDATE `movies` SET `movies`.`archived`='1' WHERE `movies`.`year`<'1984' LIMIT 10
```
And the ``orderBy()`` clause:
```php
$query->table('movies')
    ->where('year', '<', 1984)
    ->update([
        'archived' => 1
    ])
    ->orderBy('year', 'desc')
    ->limit(10);
// UPDATE `movies` SET `movies`.`archived`='1' WHERE `movies`.`year`<'1984' ORDER BY `movies`.`year` DESC LIMIT 10
```
