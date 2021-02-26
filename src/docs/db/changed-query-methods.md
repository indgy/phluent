# Changes from Query

### Additional aggregate methods
In addition to the Query aggregate methods which can still be used, the following methods will return a numeric value of an aggregate function; `getAvg()`, `getMin()`, `getMax()`, `getSum()`. 
```php
$result = $db->table('movies')->getAvg('year');
// 1996
$result = $db->table('movies')->getMax('year');
// 2020
$result = $db->table('movies')->getMin('year');
// 1921
```

The ``getCount()`` method now returns an Integer representing the number of rows in the result.
```php
$result = $db->table('movies')->getCount();
// 4803
$result = $db->table('movies')->where('title', 2001)->getCount();
// 1
```

The `getExists()` method is similar to `getCount()` but returns a Boolean value.
```php
$result = $db->table('movies')->where('title', 2001)->getExists();
// true
$result = $db->table('movies')->where('title', 5001)->getExists();
// false
```

### Data operations
The `insert()`, `update()` and `delete()` methods now operate on the table returning the number of rows affected by the operation. 
```php
$affected_rows = $db->insert($items);
$affected_rows = $db->update($items);
$affected_rows = $db->delete($items);
```

Use the `insertGetId()` method to return the id of the new row.
```php
$item->id = $db->insertGetId($item);

// using a loop to get multiple ids
foreach ($items as $item) {
    $item->id = $db->insertGetId($item);
}
```
