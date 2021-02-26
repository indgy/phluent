# Phluent

A dependency free lightweight fluent SQL query builder using PDO allowing you to interact with your database as follows: 

```php
$query->select('title, year')
    ->from('movies')
    ->where(function($query) {
        $query->where('title', 'like' ,'A %');
        $query->orWhere('title', 'like' ,'The %');
    })
    ->orderBy('year');
```

<!-- An extension handling relationships is planned for release, this uses the Data Mapper & Entity paradigm rather than Active Record. -->

## Install

Phluent can be used as a composer package or a single file download and included() into your project.

**Via Composer**

``` bash
$ composer require indgy/phluent
```

**As a standalone file**

```bash
cp dist/Phluent.php /path/to/your/project
```
Then require it where needed in your project

```php
require("/path/to/your/project/Phluent.php");
```

## Usage

See the [documentation](https://indgy.github.io/phluent) for details.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ make test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- [Indgy](https://indgy.uk)
<!-- - [All Contributors][link-contributors] -->

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/indgy/phluent.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/indgy/phluent/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/indgy/phluent.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/indgy/phluent.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/indgy/phluent.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/indgy/phluent
[link-travis]: https://travis-ci.org/indgy/phluent
[link-scrutinizer]: https://scrutinizer-ci.com/g/indgy/phluent/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/indgy/phluent
[link-downloads]: https://packagist.org/packages/indgy/phluent
[link-author]: https://github.com/:author_username
[link-contributors]: ../../contributors