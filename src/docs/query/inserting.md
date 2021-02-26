# Inserting rows
The ``insert()`` method method generates SQL to insert values into the table rows.

!!! Important
    The ``insert()`` method should be called last.

### Inserting a single item
Pass an associative array representing the columns and values to be inserted:
```php
$query->table('movies')->insert([
    'title' => '2001',
    'year' => 1968,
    'rating' => 8.3
]);
// INSERT INTO `movies` (`title`,`year`,`rating`) VALUES ('2001', '1968', '8.3');
```
You may also pass in an object:
```php
$movie = new StdClass;
$movie->title = '2001';
$movie->year = 1968;
$movie->rating = 8.3;

$query->table('movies')->insert($movie);
// INSERT INTO `movies` (`title`,`year`,`rating`) VALUES ('2001', '1968', '8.3');
```
### Inserting multiple items
Pass an array of associative arrays:
```php
$query->table('movies')->insert([
    [
        'title' => '2001',
        'year' => 1968,
        'rating' => 8.3
    ],
    [
        'title' => 'The Shining',
        'year' => 1980,
        'rating' => 8.4
    ],
    [
        'title' => 'Clockwork Orange',
        'year' => 1971,
        'rating' => 8.3
    ]
]);
// INSERT INTO `movies` (`title`,`year`,`rating`) VALUES ('2001', '1968', '5'),('The Shining', '1980', '8.4'),('Clockwork Orange', '1971', '8.3');
```
You may also pass an array of objects:
```php
$query->table('movies')->insert([
    new class {
        public $title = '2001';
        public $year = 1968;
        public $rating = 8.3;
    },
    new class {
        public $title = 'The Shining';
        public $year = 1980;
        public $rating = 8.4;
    },
    new class {
        public $title = 'Clockwork Orange';
        public $year = 1971;
        public $rating = 8.3;
    }
]);
// INSERT INTO `movies` (`title`,`year`,`rating`) VALUES ('2001', '1968', '5'),('The Shining', '1980', '8.4'),('Clockwork Orange', '1971', '8.3');
```
