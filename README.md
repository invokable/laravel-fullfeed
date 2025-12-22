# FullFeed

## Overview

FullFeed is a Laravel package that extracts the main content from web pages for use in feed readers.  
It uses site-specific rules defined in a JSON file to parse and retrieve exactly the content you need.

We've separated the FullFeed package from our private feed reader app and made it publicly available.

## Requirements

- PHP >= 8.4
    - Since using `Dom\HTMLDocument`, must be 8.4 or higher.
- Laravel >= 12.x

## Installation

```shell
composer require revolution/laravel-fullfeed
```

Publish config and site definition files

```shell
php artisan vendor:publish --tag=fullfeed
```

`config/fullfeed.php` and `resources/fullfeed` will be created.

## Configuration

If you want to add your own site rules, add them in `resources/fullfeed`.

## Usage

```php
use Revolution\Fullfeed\Facades\FullFeed;

$html = FullFeed::get($url);
```

## Testing

```php
use Revolution\Fullfeed\Facades\FullFeed;

FullFeed::expects('get')
    ->with('https://example.com/article/1')
    ->andReturn('<div>Main content</div>');

// Your test code here
```

## License

MIT
