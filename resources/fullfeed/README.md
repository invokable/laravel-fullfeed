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
- callable: You can specify a custom class when simple selectors or XPath cannot handle the extraction. Specify it like `App\\FullFeed\\CustomExtractor`, or you can provide multiple classes in an array `["App\\FullFeed\\CustomExtractor"]`. Refer to `src/Extractor/TogetterExtractor.php` as a sample.

You can use selectors supported by PHP 8.4+'s `Dom\HTMLDocument`. After selecting with `querySelector`, it returns the first item.

## How to Add

Create a JSON file in `resources/fullfeed` and add it to `paths` in `config/fullfeed.php`.
The first rule that matches `data.url` will be used, so it's recommended to place additional files at the beginning of `paths`.
