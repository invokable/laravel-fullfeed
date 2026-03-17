---
on:
  schedule: weekly
  workflow_dispatch:

steps:
    - name: Set up PHP
      uses: shivammathur/setup-php@2.37.0
      with:
          php-version: 8.5
          extensions: mbstring
          coverage: xdebug

    - name: Install Composer dependencies
      run: composer install --no-interaction --prefer-dist --optimize-autoloader

permissions:
  contents: read
  issues: read
  pull-requests: read

strict: false

tools:
  github:
    toolsets: [default]
  web-fetch:
  cache-memory: true

network:
  allowed:
    - "*"

imports:
  - ../agents/port-rules.agent.md

safe-outputs:
  create-pull-request:
    title-prefix: "[port] "
    labels: [automation]
    draft: true
---

# LDRFullFeed ルール移植

items_all.json のルールを plus.json に移植する週次ワークフローです。
毎回1つのURLだけを処理します。

## 手順

### 1. ファイルの読み込み

以下の3つのファイルを読み込んでください:

- `resources/fullfeed/items_all.json` — LDRFullFeed のルール一覧（移植元）
- `resources/fullfeed/plus.json` — このパッケージ独自のルール（移植先）
- `.github/port.md` — 移植作業の記録ファイル

### 2. 移植対象の選定

以下の条件で移植対象を**1つだけ**選んでください:

1. `items_all.json` 内のルールから候補を選ぶ
2. `plus.json` に同じ `data.url` パターンのルールが既にあるものは除外
3. `.github/port.md` の「移植完了」「今後無視するURL」に記載されているものは除外
4. 残った候補から1つを選ぶ（先頭から順に処理）

### 3. 移植するURLがない場合

全てのルールが移植済みまたは無視リストに入っている場合は、何もせずに終了してください。
PRも作成しません。

### 4. 移植作業

選定した1つのURLについて、カスタムエージェントの指示に従って移植作業を行ってください:

1. URLの到達確認
2. 繋がらない場合: port.md を更新し、items_all.json からルールを削除
3. 繋がる場合: HTMLを解析して最適なCSS selectorを決定し、plus.json に新規ルールを作成。items_all.json からルールを削除
4. plus.json を変更した場合は `vendor/bin/testbench fullfeed:sort` を実行
5. port.md を更新

### 5. cache-memory の更新

処理した内容を cache-memory に保存してください。以下の情報を `port-progress.json` として保存:

```json
{
  "last_processed": "サイト名",
  "last_processed_url": "^https://example\\.com/",
  "last_processed_date": "YYYY-MM-DD",
  "result": "ported" or "ignored",
  "total_ported": 数値,
  "total_ignored": 数値
}
```

### 6. PR作成

変更がある場合のみPRを作成してください。PRの本文には以下を含めてください:

- 移植したサイト名とURLパターン
- 結果（移植完了 or 接続不可で無視リストに追加）
- 繋がった場合: 作成したCSS selectorと、選定理由の簡単な説明
