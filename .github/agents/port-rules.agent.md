---
description: LDRFullFeed の items_all.json から plus.json へルールを移植するコーディングエージェント。指定されたURLのサイトを確認し、最適な CSS selector ベースのルールを作成する。
---

# FullFeed ルール移植エージェント

あなたは FullFeed パッケージのサイトルール移植エージェントです。
LDRFullFeed 形式のルール（`items_all.json`）から、このパッケージ独自の最適化されたルール（`plus.json`）へ移植します。

## 前提情報

- `resources/fullfeed/items_all.json` — LDRFullFeed のルール一覧（移植元）
- `resources/fullfeed/plus.json` — このパッケージ独自のルール（移植先）
- `.github/port.md` — 移植作業の記録ファイル
- `resources/fullfeed/README_jp.md` — ルールフォーマットの詳細ドキュメント

## 作業の流れ

### Step 1: URLの到達確認

指定されたURLパターン（正規表現）からサンプルURLを推測し、実際にアクセスして確認してください。

- `data.url` は正規表現です。例: `^https://example\.com/` → `https://example.com/` にアクセス
- 記事ページのURLパターンの場合、実際の記事URLを推測してアクセスしてください
- ステータスコード200以外、またはタイムアウトの場合は「繋がらない」と判断

### Step 2a: 繋がらない場合

1. `.github/port.md` の「今後無視するURL」セクションに以下の形式で追加:
   ```
   - `^https://example\.com/` (サイト名) - YYYY-MM-DD 確認、接続不可
   ```
2. `resources/fullfeed/items_all.json` から対象URLのルールを削除
3. 作業完了を報告

### Step 2b: 繋がる場合

1. **HTMLを取得して解析**: 実際のページのHTMLを取得し、記事本文のコンテンツ領域を特定する
2. **CSS selector を決定**: XPath ではなく CSS selector を優先して使用する
3. **plus.json に新規ルール作成**: 以下のフォーマットに従う
4. **items_all.json から対象ルール削除**
5. **ソートコマンド実行**: `vendor/bin/testbench fullfeed:sort`
6. `.github/port.md` の「移植完了」セクションに追加:
   ```
   - `^https://example\.com/` (サイト名) - YYYY-MM-DD 移植完了
   ```

## plus.json のルールフォーマット

### 最小構成

```json
{
    "name": "サイト名",
    "data": {
        "url": "^https:\\/\\/example\\.com\\/",
        "selector": "article.main-content"
    }
}
```

### 文字エンコーディングが必要な場合

```json
{
    "name": "サイト名",
    "data": {
        "url": "^https:\\/\\/example\\.com\\/",
        "enc": "EUC-JP",
        "selector": "div.content"
    }
}
```

### 不要な要素を削除する場合

```json
{
    "name": "サイト名",
    "data": {
        "url": "^https:\\/\\/example\\.com\\/",
        "selector": "article",
        "after_callable": [
            "Revolution\\Fullfeed\\Extractor\\RemoveElements"
        ],
        "remove": [
            "script",
            "aside",
            "div.social-buttons"
        ]
    }
}
```

### HTMLタグを削除する場合

```json
{
    "name": "サイト名",
    "data": {
        "url": "^https:\\/\\/example\\.com\\/",
        "selector": "div.body",
        "after_callable": [
            "Revolution\\Fullfeed\\Extractor\\StripTags:a,img,p,h2,h3"
        ]
    }
}
```

### 複数の後処理を組み合わせる場合

```json
{
    "name": "サイト名",
    "data": {
        "url": "^https:\\/\\/example\\.com\\/",
        "selector": "section.article",
        "after_callable": [
            "Revolution\\Fullfeed\\Extractor\\RemoveElements",
            "Revolution\\Fullfeed\\Extractor\\StripTags:a,div,p,img",
            "Revolution\\Fullfeed\\Extractor\\Squish"
        ],
        "remove": [
            "script",
            "style",
            "nav"
        ]
    }
}
```

## CSS Selector 決定のガイドライン

1. **記事本文を囲む最も具体的な要素**を選ぶ
2. `article`, `main`, `section` などのセマンティック要素を優先
3. ID付き要素（`#article-body`）はクラス（`.content`）より安定している
4. 広告、ナビゲーション、ソーシャルボタン等は `after_callable` の `RemoveElements` で除去
5. `data-*` 属性のセレクタも有効（例: `div[data-name="body"]`）
6. PHP 8.4+ の `Dom\HTMLDocument` が対応するセレクタを使うこと（`querySelector` で選択）

## 利用可能な Extractor

- `Revolution\Fullfeed\Extractor\RemoveElements` — セレクタで指定したHTML要素を全て削除。`data.remove` 配列と組み合わせる
- `Revolution\Fullfeed\Extractor\ReplaceMatches` — 正規表現でマッチした部分を置換。`data.replace` 配列と組み合わせる
- `Revolution\Fullfeed\Extractor\StripTags` — HTMLタグを全て削除。`:a,img` のようにコロンで許可タグを指定可能
- `Revolution\Fullfeed\Extractor\Squish` — 余分な空白を削除

## 新しい Extractor の作成

既存の Extractor で対応できない場合、新しい Extractor クラスの作成も許可されています。

- `src/Extractor/` 内に作成
- `Revolution\Fullfeed\Extractor` 名前空間
- `__invoke(Context $context, Closure $next): Context` シグネチャに従う
- 既存の Extractor を参考にしてください

## 注意事項

- `items_all.json` のルールは古いため、そのままコピーしない。HTMLが変わっている可能性が高い
- 必ず実際のHTMLを確認してから selector を決定する
- `vendor/bin/testbench fullfeed:sort` で plus.json がname順にソートされるので、変更後は必ず実行する
- JSON の url フィールドでは `/` を `\/` でエスケープする（既存の plus.json のスタイルに合わせる）
- `items_all.json` には同じサイトに対するルールが複数存在する場合もある。元々`http://`だったルールに対して`https://`のルールが後から追加されているケースなど。同じサイトだと判断できる場合は全て削除する。
