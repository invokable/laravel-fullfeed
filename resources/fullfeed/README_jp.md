# サイトルールファイル

## items_all.json

日本では一般的なLDRFullFeed
http://wedata.net/databases/LDRFullFeed/items_all.json

全文取得機能はほとんどこれが使われているのでこのパッケージでもそのまま参考にしています。

## plus.json

LDRFullFeedはもうあまり更新されてないので独自に追加も可能です。
plus.jsonは追加ファイルのサンプル。

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

実際に使うのはdata内

- url: 対象URLの正規表現
- selector: CSSセレクタ。LDRFullFeedとは違ってセレクタにも対応。XPathより優先される。
- xpath: XPath。LDRFullFeedからそのまま使う場合。LDRFullFeedにあるけどサイト側が変わっていて使えなくなってる場合にはコピペして修正して使う。
- enc: 文字エンコーディング。UTF-8以外の文字コードを使っていて変換が必要な場合に指定。
- callable: 単純なselectorやxpathでは対応できない場合に独自のクラスを指定可能。`App\\FullFeed\\CustomExtractor`のように一つだけ指定も配列`["App\\FullFeed\\CustomExtractor"]`で複数指定も可能。`src/Extractor/TogetterExtractor.php`がサンプルなので参考にしてください。
- after_callable: callableと同様ですが、Extractorの最後に実行されます。RemoveElementsやReplaceMatchesなどで不要な要素を削除・置換したい場合に使えます。

PHP8.4以降の`Dom\HTMLDocument`が対応しているセレクタを使えます。`querySelector`で選択した最初のアイテムを返します。

## Extractor

Extractorの実行順は以下のようになります。

1. callableで指定したクラス
2. XPathExtractor
3. SelectorExtractor
4. after_callableで指定したクラス

### RemoveElements

セレクタで指定したhtml要素を全て削除。

```json
            "after_callable": ["Revolution\\Fullfeed\\Extractor\\RemoveElements"],
            "remove": [
                "svg",
                "button",
                "script"
            ],
```

### ReplaceMatches

正規表現でマッチした部分を置換。htmlを文字列のまま処理するのがRemoveElementsとの違いです。

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
            ]
```

## 追加方法
`resources/fullfeed`内にjsonファイルを作ってから`config/fullfeed.php`の`paths`に追加してください。
`data.url`にマッチした最初のルールが使われるので追加ファイルは`paths`の先頭に置くのが良いでしょう。
