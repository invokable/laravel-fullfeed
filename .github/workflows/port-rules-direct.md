---
on:
  workflow_dispatch:

engine:
  id: copilot

strict: false

sandbox:
  agent: false

permissions:
  contents: read
  issues: read
  pull-requests: read

tools:
  github:
    toolsets: [default]
  bash: [":*"]
  edit:
  web-fetch:
  cache-memory: true

safe-outputs:
  threat-detection: false
  create-pull-request:
    title-prefix: "[port] "
    labels: [automation]
    draft: true
    fallback-as-issue: false
---

# LDRFullFeed ルール移植（直接作業）

`sandbox: agent: false` により任意のURLに直接アクセスし、1つのルールを移植して PR を作成します。

## 手順

### 1. ファイルの読み込み

以下のファイルを読み込んでください:

- `resources/fullfeed/items_all.json` — 移植元ルール一覧
- `resources/fullfeed/plus.json` — 移植先ルール
- `.github/port.md` — 作業記録

### 2. 既存issueの確認

タイトルが `[port]` で始まるオープン中のissueを検索してください。
そのissueで作業中のルールは選定対象から除外します。

### 3. 移植対象の選定

以下の条件で移植対象を**1つだけ**選んでください:

1. `items_all.json` 内のルールから候補を選ぶ
2. `plus.json` に同じ `data.url` パターンのルールが既にあるものは除外
3. `.github/port.md` の「移植完了」「今後無視するURL」に記載されているものは除外
4. 既存の `[port]` issueで作業中のものは除外
5. 残った候補から1つを選ぶ（先頭から順に処理）

移植するURLがない場合は、何もせずに終了してください。

### 4. URLの到達確認

`data.url` の正規表現からサンプルURLを推測し、`curl` でアクセスして確認してください:

```bash
curl -L -s -o /dev/null -w "%{http_code}" --max-time 15 "https://example.com/"
```

- ステータスコード 200 → 繋がる
- それ以外、またはタイムアウト → 繋がらない

### 5a. 繋がらない場合

1. `.github/port.md` の「今後無視するURL」に追加:
   ```
   - `^https://example\.com/` (サイト名) - YYYY-MM-DD 確認、接続不可
   ```
2. `resources/fullfeed/items_all.json` から対象URLのルールを削除（同サイトの複数ルールも全て削除）
3. cache-memory の `port-progress.json` を更新
4. PR を作成

### 5b. 繋がる場合

1. **HTMLを取得して解析**: 実際の記事ページのHTMLを取得し、記事本文の領域を特定する

   記事URLが不明な場合はトップページから記事一覧を探してサンプル記事にアクセスしてください:

   ```bash
   curl -L -s --max-time 15 "https://example.com/article/" | head -300
   ```

2. **CSS selector を決定**: `resources/fullfeed/plus.json` のフォーマットに従う

   - 記事本文を囲む最も具体的な要素を選ぶ
   - `article`, `main`, `section` などのセマンティック要素を優先
   - ID付き要素（`#article-body`）はクラス（`.content`）より安定
   - 広告、ナビゲーション等は `RemoveElements` で除去
   - PHP 8.4+ の `Dom\HTMLDocument` が対応するセレクタを使うこと
   - `items_all.json` のルールは古いためそのままコピーしない。実際のHTMLを確認する

3. `resources/fullfeed/plus.json` に新規ルールを追加（`url` フィールドでは `/` を `\/` でエスケープ）

4. `resources/fullfeed/items_all.json` から対象ルールを削除（同サイトの複数ルールも全て削除）

5. ソートコマンドを実行:
   ```bash
   vendor/bin/testbench fullfeed:sort
   ```

6. `.github/port.md` の「移植完了」に追加:
   ```
   - `^https://example\.com/` (サイト名) - YYYY-MM-DD 移植完了
   ```

7. cache-memory の `port-progress.json` を更新

8. PR を作成

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

## cache-memory の更新

処理した内容を `port-progress.json` として保存:

```json
{
  "last_processed": "サイト名",
  "last_processed_url": "^https://example\\.com/",
  "last_processed_date": "YYYY-MM-DD"
}
```
