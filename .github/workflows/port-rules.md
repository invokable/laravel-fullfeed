---
on:
  schedule: daily around 3:00 utc+9
  workflow_dispatch:

engine:
  id: copilot

permissions:
  contents: read
  issues: read
  pull-requests: read

tools:
  github:
    toolsets: [default]
  cache-memory: true

safe-outputs:
  create-issue:
    title-prefix: "[port] "
    labels: [automation]
    max: 1
---

# LDRFullFeed ルール移植

items_all.json のルールを plus.json に移植する日次ワークフローです。
毎回1つのURLだけを処理し、Copilot コーディングエージェントに移植作業を割り当てます。

## 手順

### 1. ファイルの読み込み

以下の3つのファイルを読み込んでください:

- `resources/fullfeed/items_all.json` — LDRFullFeed のルール一覧（移植元）
- `resources/fullfeed/plus.json` — このパッケージ独自のルール（移植先）
- `.github/port.md` — 移植作業の記録ファイル

### 2. 既存issueの確認

タイトルが `[port]` で始まるオープン中のissueを検索してください。

- **オープン中のissueが存在する場合**: 既存issueで作業するルールは選定対象から除外します。ルールを確認したら次のステップに進んでください。
- **オープン中のissueがない場合**: 次のステップに進んでください。

### 3. 移植対象の選定

以下の条件で移植対象を**1つだけ**選んでください:

1. `items_all.json` 内のルールから候補を選ぶ
2. `plus.json` に同じ `data.url` パターンのルールが既にあるものは除外
3. `.github/port.md` の「移植完了」「今後無視するURL」に記載されているものは除外
4. 残った候補から1つを選ぶ（先頭から順に処理）

### 4. 移植するURLがない場合

全てのルールが移植済みまたは無視リストに入っている場合は、何もせずに終了してください。
issueも作成しません。

### 5. issueの作成

選定した1つのURLについて issue を作成してください。Copilotへのアサインは手動で行います。

**issueのタイトル**: `ルール移植: {サイト名}`

**issueの本文**に以下を含めてください:

- 移植対象の `name` と `data.url` パターン
- `items_all.json` 内のルールの現在のデータ（xpath, enc 等）
- 移植作業の指示:
  1. URLの到達確認
  2. 繋がらない場合: `.github/port.md` の「今後無視するURL」に追加し、`items_all.json` からルール削除
  3. 繋がる場合: HTMLを解析して最適なCSS selectorを決定し、`plus.json` に新規ルール作成。`items_all.json` からルール削除。`vendor/bin/testbench fullfeed:sort` を実行。`.github/port.md` の「移植完了」に追加

### 6. cache-memory の更新

処理した内容を cache-memory に保存してください。以下の情報を `port-progress.json` として保存:

```json
{
  "last_processed": "サイト名",
  "last_processed_url": "^https://example\\.com/",
  "last_processed_date": "YYYY-MM-DD"
}
```
