---
on:
  #schedule: daily around 2:00 utc+9 これだけ実行が失敗しているので一時的に停止
  workflow_dispatch:

engine:
  id: copilot
  model: claude-haiku-4.5
  agent: port-rules

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

jobs:
    setup-php:
        runs-on: ubuntu-latest
        steps:
            - name: Checkout code
              uses: actions/checkout@v6
            - name: Set up PHP
              uses: shivammathur/setup-php@2.37.0
              with:
                  php-version: 8.5
                  extensions: mbstring, dom
                  coverage: xdebug
            - name: Install Composer dependencies
              run: composer install --no-interaction --prefer-dist --optimize-autoloader

safe-outputs:
  threat-detection: false
  create-pull-request:
    title-prefix: "[port] "
    labels: [port]
    draft: true
    fallback-as-issue: true
    protected-files: "allowed"
---

# LDRFullFeed ルール移植（直接作業）

任意のURLに直接アクセスし、1つのルールを移植して PR を作成します。
ルールのフォーマットや移植手順はエージェントの知識（port-rules agent）に従ってください。

## 手順

### 1. ファイルの読み込み

- `resources/fullfeed/items_all.json` — 移植元ルール一覧
- `resources/fullfeed/plus.json` — 移植先ルール
- `.github/port.md` — 作業記録

### 2. 移植対象の選定

以下の条件で移植対象を**1つだけ**選んでください:

1. `items_all.json` 内のルールから候補を選ぶ
2. `plus.json` に同じ `data.url` パターンのルールが既にあるものは除外
3. `.github/port.md` の「移植完了」「今後無視するURL」に記載されているものは除外
4. 既存のプルリクエストで作業中のものは除外
5. 残った候補から1つを選ぶ（先頭から順に処理）

移植するURLがない場合は何もせずに終了してください。

### 3. URLの到達確認

`data.url` の正規表現からドメインの**トップページURL**を推測し、`curl` で確認してください。
パスを含む正規表現（例: `/archives/`, `/article/`）でも確認対象はトップページです:

```bash
curl -L -s -o /dev/null -w "%{http_code}" --max-time 15 "https://example.com/"
```

- 200 または正常なリダイレクト先 → **繋がる**
- タイムアウト・DNS失敗・ドメインパーキング → **繋がらない**
- 404 はパスの問題なので「繋がらない」と判定しない

### 4. 移植作業

エージェントの知識（Step 2a / Step 2b）に従って作業してください:

- 繋がらない場合: `.github/port.md` の「今後無視するURL」に追加し、`items_all.json` から削除
- 繋がる場合: HTMLを取得・解析してCSS selectorを決定し、`plus.json` に新規ルール作成、`items_all.json` から削除、`vendor/bin/testbench fullfeed:sort` を実行、`.github/port.md` の「移植完了」に追加

### 5. cache-memory の更新と PR 作成

処理した内容を `port-progress.json` として保存し、PR を作成してください:

```json
{
  "last_processed": "サイト名",
  "last_processed_url": "^https://example\\.com/",
  "last_processed_date": "YYYY-MM-DD"
}
```
