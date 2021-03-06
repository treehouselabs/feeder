Feeder
======

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

Library containing functions to download, parse, transform and export different types of feeds.

## Installation

```sh
composer require treehouselabs/feeder:~1.0
```

## Usage

Here's a simple feed processing script:

```php
// create a new reader, in this case we'll fetch a feed from the interwebs
$resource = new FileResource(HttpTransport::create('http://example.org/feed'));
$reader = new XmlReader($resource);

// tell the reader to pull <item> nodes
$reader->setNodeCallback('item');

// create a feed for this reader
$feed = new Feed($reader);

// now simply iterate over the items
foreach ($feed as $item) {
    // $item is a ParameterBag instance with the serialized <item> node as data
}
```

## Modifiers

Feeder becomes really powerful when you start adding modifiers. Modifiers are applied to each item and can be used to
transform field values, remap keys, remove/add new values, etc.

Say we have the following `<item>` node in the feed we want to process:

```xml
<item>
  <title>The quick brown fox jumps over the lazy dog</title>
  <publishDate>Thu, 05 Mar 2015 20:24:38 +0000</publishDate>
  <explicit>yes</explicit>
  <link href="http://example.org/articles/1"/>
</item>
```

This will give the following item:

```php
[
  'title' => 'The quick brown fox jumps over the lazy dog',
  'publishDate' => 'Thu, 05 Mar 2015 20:24:38 +0000',
  'explicit' => 'yes',
  'link' => [
    '#'     => '',
    '@href' => 'http://example.org/articles/1'
  ],
]
```

If for some reason your OCD-levels are like mine and you want data to be snake_cased:

```php
$feed->addTransformer(new LowercaseKeysTransformer());

// will return:
[
  'title' => 'The quick brown fox jumps over the lazy dog',
  'publish_date' => 'Thu, 05 Mar 2015 20:24:38 +0000',
  'explicit' => 'yes',
  'link' => [
    '#'     => '',
    '@href' => 'http://example.org/articles/1'
  ],
]
```

Now we want the publish date to be an actual `DateTime` instance:

```php
// the DataTransformer wraps a transformer for a specific field,
// instead of the whole item
$transformer = new DataTransformer(
    new StringToDateTimeTransformer(DATE_RFC2822),
    'publish_date'
);
$feed->addTransformer($transformer);

// will return:
[
  'title' => 'The quick brown fox jumps over the lazy dog',
  'publish_date' => DateTime::__set_state(array(
    'date' => '2015-03-05 20:24:38.000000',
    'timezone_type' => 1,
    'timezone' => '+00:00',
  )),
  'explicit' => 'yes',
  'link' => [
    '#'     => '',
    '@href' => 'http://example.org/articles/1'
  ],
]
```

Some more examples:

```php
$feed->addTransformer(
  new DataTransformer(
    new StringToBooleanTransformer(),
    'explicit'
  )
);
$feed->addTransformer(
    new DataTransformer(
        new CallbackTransformer(function ($value) { return $value['@href']; }),
        'link'
    )
);

// will return:
[
  'title' => 'The quick brown fox jumps over the lazy dog',
  'publish_date' => DateTime::__set_state(array(
    'date' => '2015-03-05 20:24:38.000000',
    'timezone_type' => 1,
    'timezone' => '+00:00',
  )),
  'explicit' => true,
  'link' => 'http://example.org/articles/1',
]
```

As you can see you can create really powerful chains of modifiers to get the outcome you want. Modifiers are not limited
to transformative functions, there are mappers (map keys in the item to your own fields), filters (exclude items based
on your own logic) and validators (raises exceptions when an item is invalid). There are a lot of modifiers that come
with this library, [check them out][modifiers]!


## Testing

``` bash
composer test
```

## Security

If you discover any security related issues, please email peter@treehouse.nl instead of using the issue tracker.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


## Credits

- [Peter Kruithof][link-author]
- [All Contributors][link-contributors]


[modifiers]: /src/TreeHouse/Feeder/Modifier

[ico-version]: https://img.shields.io/packagist/v/treehouselabs/feeder.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/treehouselabs/feeder/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/treehouselabs/feeder.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/treehouselabs/feeder.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/treehouselabs/feeder.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/treehouselabs/feeder
[link-travis]: https://travis-ci.org/treehouselabs/feeder
[link-scrutinizer]: https://scrutinizer-ci.com/g/treehouselabs/feeder/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/treehouselabs/feeder
[link-downloads]: https://packagist.org/packages/treehouselabs/feeder
[link-author]: https://github.com/treehouselabs
[link-contributors]: ../../contributors
