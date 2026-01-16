# Site Rule Files

## items_all.json

LDRFullFeed, which is popular in Japan
http://wedata.net/databases/LDRFullFeed/items_all.json

This format is commonly used for full-text extraction, so this package also uses it as a reference.

## plus.json

Since LDRFullFeed is no longer actively updated, you can add your own rules.
plus.json is a sample additional file.

```json
    {
        "name": "note",
        "data": {
            "url": "^https://note\\.com/",
            "selector": "div[data-name=\"body\"]",
            "xpath": "//div[@data-name=\"body\"]",
            "enc": "UTF-8",
            "callable": "App\\FullFeed\\CustomExtractor"
        }
    },
```

The actual usage is within the data object:

- url: Regular expression for target URLs
- selector: CSS selector. Unlike LDRFullFeed, this also supports selectors. Takes priority over XPath.
- xpath: XPath. For direct use from LDRFullFeed. When a rule exists in LDRFullFeed but no longer works due to site changes, copy and modify it for use.
- enc: Character encoding. Specify when the site uses a character encoding other than UTF-8 that requires conversion.
- callable: You can specify a custom class when simple selectors or XPath cannot handle the extraction. Specify it like `App\\FullFeed\\CustomExtractor`, or you can provide multiple classes in an array `["App\\FullFeed\\CustomExtractor"]`. Refer to [src/Extractor](https://github.com/invokable/laravel-fullfeed/tree/main/src/Extractor) as a sample.
- after_callable: Similar to callable, but executed at the end of the Extractor process. Useful for removing or replacing unwanted elements using classes like RemoveElements or ReplaceMatches.

You can use selectors supported by PHP 8.4+'s `Dom\HTMLDocument`. After selecting with `querySelector`, it returns the first item.

## Extractor

The execution order of Extractors is as follows:

1. Classes specified in callable
2. XPathExtractor
3. SelectorExtractor
4. Classes specified in after_callable

Laravel's Pipeline processes them in order.

### RemoveElements

Removes all HTML elements specified by the selector.

```json
            "after_callable": ["Revolution\\Fullfeed\\Extractor\\RemoveElements"],
            "remove": [
                "svg",
                "button",
                "script"
            ],
```

### ReplaceMatches

Replaces text matching the specified pattern.  
The difference from RemoveElements is that it processes the HTML as a string.

```json
            "after_callable": ["Revolution\\Fullfeed\\Extractor\\ReplaceMatches"],
            "replace": [
                {
                    "pattern": "/ data-(h-)?index=\"[0-9]+\"/",
                    "replace": ""
                },
                {
                    "pattern": "/ data-id=\"[0-9]+\"/",
                    "replace": ""
                }
            ],
```

### StripTags

Performs the same operation as PHP's `strip_tags()`, removing all HTML tags.

```json
            "after_callable": ["Revolution\\Fullfeed\\Extractor\\StripTags"]
```

You can also specify tags to allow:

```json
            "after_callable": ["Revolution\\Fullfeed\\Extractor\\StripTags:a,img"]
```

### Squish

Remove extra whitespace using Laravel's `Str::squish()`.

```json
            "after_callable": ["Revolution\\Fullfeed\\Extractor\\Squish"]
```

## How to Add

Create a JSON file in `resources/fullfeed` and add it to `paths` in `config/fullfeed.php`.
The first rule that matches `data.url` will be used, so it's recommended to place additional files at the beginning of `paths`.
