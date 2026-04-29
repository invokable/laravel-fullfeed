# Plan

1. Identify the first eligible rule from `items_all.json` that is not already in `plus.json`, `.github/port.md`, or an open `[port]` issue.
2. Check the candidate site's reachability from its top page, then inspect a live article page if reachable to determine the best CSS selector and any cleanup steps.
3. Update `resources/fullfeed/items_all.json`, `resources/fullfeed/plus.json`, `.github/port.md`, and cache memory; run sorting and project checks; create a branch, commit, and open a draft PR.
