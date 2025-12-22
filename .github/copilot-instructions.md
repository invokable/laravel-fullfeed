# Project Guidelines

## Overview

FullFeed is a Laravel package that extracts the main content from web pages for use in feed readers. It uses site-specific rules defined in a JSON file to parse and retrieve exactly the content you need.

We've separated the FullFeed package from our private feed reader app and made it publicly available.

## Technology Stack
- PHP 8.4+
- Laravel 12.x
- Pest for testing
- Pint for code linting

## Commands
- `composer run test`: Run the pest test suite.
- `composer run lint`: Run code linting by pint.

## Site rule files

`resources/fullfeed` contains site rule files.

We are using the LDRFullFeed format, which is popular in Japan, as a reference. Only a portion of the LDRFullFeed format is actually used, and the minimum rules are as follows:

```json
    {
        "name": "note",
        "data": {
            "url": "^https://note\\.com/",
            "selector": "div[data-name=\"body\"]",
            "xpath": "//div[@data-name=\"body\"]",
            "enc": "UTF-8"
        }
    },
```
