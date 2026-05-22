# `all_articles` → `category_members` — Migration Feasibility Report

**Audit date:** 2026-05-22
**Scope:**
- [Mdwiki-TD/TD_API](https://github.com/Mdwiki-TD/TD_API)
- [Mdwiki-TD/Translation-Dashboard](https://github.com/Mdwiki-TD/Translation-Dashboard)
- [Mdwiki-TD/mdwiki-python-files](https://github.com/Mdwiki-TD/mdwiki-python-files) (excluding `src/sqlalchemy_sql/`)

**Reference schemas**

```sql
CREATE TABLE all_articles (
  id         int          NOT NULL AUTO_INCREMENT,
  article_id varchar(255) NOT NULL,
  category   varchar(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY article_id (article_id)            -- 1 row per article, single (nullable) category
);

CREATE TABLE category_members (
  id         int          NOT NULL AUTO_INCREMENT,
  category   varchar(120) NOT NULL,             -- NOT NULL, FK → categories(category)
  article_id varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY category_article_id (category, article_id),  -- many categories per article
  KEY article_id (article_id),
  CONSTRAINT category_members_ibfk_1
    FOREIGN KEY (category) REFERENCES categories (category)
);
```

The semantic gap is the key risk in every rewrite:

| Aspect | `all_articles` | `category_members` |
|---|---|---|
| Cardinality per article | 1 row (UNIQUE article_id) | N rows (one per category) |
| `category` nullability | Nullable | NOT NULL + FK |
| Identity column for joins | `article_id` is unique | `article_id` is *not* unique |

---

## 1. Executive Summary

| Repository | Live queries / code blocks | Schema / docs only |
|---|---:|---:|
| TD_API | 1 (PHP `LEFT JOIN`) | `sql.sql` DDL, `docs/sql_tables_deleted.md`, `refactor.md` (refers to deleted view `all_articles_titles`) |
| Translation-Dashboard | 1 (PHP `LEFT JOIN`) | `sql.sql` DDL |
| mdwiki-python-files | 5 (1 INSERT, 4 SELECT) | — |
| **Total live** | **7** | |

**Overall verdict: PARTIAL → FEASIBLE with a small refactor.**

- All 7 active queries can be rewritten to use `category_members`.
- 6 of 7 require only a mechanical change (table rename + `DISTINCT` / row-multiplication handling).
- 1 query path (`fix_it_db.py`) is already broken (it reads `w_lead_words` / `w_all_words` columns that do not exist in the current `all_articles` schema). The migration to `category_members` does not fix it; the right action is to retire that code path or re-point it at the `words` table.
- No live foreign key in either `sql.sql` references `all_articles`, so dropping the table will not break referential integrity. The historical FK from `all_exists.article_id → all_articles.article_id` is documented as already removed in `docs/sql_tables_deleted.md`.

After applying the rewrites below `all_articles` can be **fully dropped**.

---

## 2. Query Inventory

### 2.1 TD_API

| # | File | Line(s) | Operation | Columns Used | category nullable? | Notes |
|---|------|---------|-----------|-------------|-------------------|-------|
| T1 | `src/api_cod/subs/missing_exists.php` | 36 (within `exists_by_qids_query`, plus the `aa.category` filter at 60–66) | SELECT (`LEFT JOIN`) | `article_id`, `category` | Yes — `LEFT JOIN` may produce NULL `aa.category` for rows that don't match | Used to attach (and optionally filter on) a single category per article |
| T1-doc | `sql.sql` | 33–40 | DDL | — | — | Schema definition; remove on cutover |
| T1-doc | `docs/sql_tables_deleted.md` | 41–65, 122, 126–138 | Documentation | — | — | Documents already-deleted views and the still-live `all_articles` table |
| T1-doc | `refactor.md` | 845 | Planned ORM call | — | — | References deleted **view** `all_articles_titles`, not the table; out of scope but flagged for cleanup |

The other functions in `missing_exists.php` (`exists_statics_by_category`, `missing_by_lang_and_category`, `exists_by_lang_and_category`, `statics_by_category`) **already query `category_members`**, so the migration is partially done in this repo.

### 2.2 Translation-Dashboard

| # | File | Line(s) | Operation | Columns Used | category nullable? | Notes |
|---|------|---------|-----------|-------------|-------------------|-------|
| D1 | `src/backend/api_or_sql/new_sql_tables.php` | 53 (within `exists_by_qids_query`) | SELECT (`LEFT JOIN`) | `article_id`, `category` | Yes — same `LEFT JOIN` semantics as T1 | Verbatim copy of TD_API's `exists_by_qids_query` |
| D1-doc | `sql.sql` | 33–39 | DDL | — | — | Schema definition |

The neighbouring functions in `new_sql_tables.php` (`missing_by_lang_and_category`, `exists_by_lang_and_category`, `count_category_members`, `statics_by_category`) **already query `category_members`**.

### 2.3 mdwiki-python-files (excluding `src/sqlalchemy_sql/`)

| # | File | Line(s) | Operation | Columns Used | category nullable? | Notes |
|---|------|---------|-----------|-------------|-------------------|-------|
| P1 | `src/td_core/copy_data/by_title/all_articles.py` | 87 (`to_sql(... "all_articles" ...)`); SELECT also implicitly emitted by the `to_sql` helper at `src/td_core/mdpyget/bots/to_sql.py:152` | INSERT + UPDATE (upsert via the helper) | `article_id`, `category` | No — `category` is always set from the `{title: cat}` dict | Same script ALSO writes the canonical many-to-many data into `category_members` (lines 50–65). The `all_articles` write is effectively redundant. |
| P2 | `src/td_core/fix_user_pages/fix_it_db.py` | 15 (`get_all_from_table(table_name="all_articles")` → `SELECT DISTINCT * FROM all_articles`); used at lines 39–43 | SELECT | Reads `article_id`; **also reads `w_lead_words` / `w_all_words` which do not exist on `all_articles`** | N/A | Code path is broken: `all_articles` does not have word-count columns. Lookup also assumes `article_id` is globally unique. |
| P3 | `src/td_core/mdcount/countref.py` | 62 (`from_sql`) | SELECT | `article_id` | N/A | Used to enumerate the working set of titles |
| P4 | `src/td_core/mdcount/countrefs_and_words.py` | 76 (`from_sql`) | SELECT | `article_id` | N/A | Identical pattern to P3 |
| P5 | `src/td_core/mdcount/words.py` | 59 (`from_sql`) | SELECT | `article_id` | N/A | Identical pattern to P3 |

---

## 3. Migration Analysis — per query

### T1 / D1 — `LEFT JOIN all_articles aa ON aa.article_id = q.title`

**Original query** (TD_API `src/api_cod/subs/missing_exists.php:13–67`, mirrored in Translation-Dashboard `src/backend/api_or_sql/new_sql_tables.php:18–66`):

```sql
SELECT
    t.qid AS qid,
    q.title AS title,
    aa.category AS category,
    t.code AS code,
    t.target AS target
FROM qids q
    JOIN all_qids_exists t      ON t.qid = q.qid
    LEFT JOIN all_articles aa   ON aa.article_id = q.title
WHERE t.code = ?
  AND (t.target != '' AND t.target IS NOT NULL)
-- + optional:
--   AND aa.category IN (SELECT category FROM categories WHERE campaign = ?)
--   AND aa.category = ?
```

**Rewritten equivalent using `category_members`:**

Because `category_members` can carry many rows per `article_id`, a naive substitution will multiply rows. Two acceptable rewrites depending on the intent:

**Option A — explicit category (or campaign) filter:** join + filter and accept one row per (qid, category) pair, which is the natural shape of the data anyway:

```sql
SELECT
    t.qid AS qid,
    q.title AS title,
    cm.category AS category,
    t.code AS code,
    t.target AS target
FROM qids q
    JOIN all_qids_exists t       ON t.qid = q.qid
    LEFT JOIN category_members cm ON cm.article_id = q.title
WHERE t.code = ?
  AND (t.target != '' AND t.target IS NOT NULL)
  AND ( ? IS NULL OR cm.category = ? )                 -- when category=…
  -- or:
  -- AND ( ? IS NULL OR cm.category IN
  --       (SELECT category FROM categories WHERE campaign = ?) )
```

**Option B — single representative category, preserving the original "1 row per qid" cardinality:** aggregate on the join side:

```sql
LEFT JOIN (
    SELECT article_id, MIN(category) AS category   -- or GROUP_CONCAT
    FROM category_members
    GROUP BY article_id
) cm ON cm.article_id = q.title
```

- **category nullability after migration:** still nullable in the result set (unmatched articles → NULL via `LEFT JOIN`). FK on `category_members.category` is satisfied at write time, not at read time, so it does not constrain SELECTs.
- **Risk level: MEDIUM.** Trivial syntactic change, but the engineer must consciously pick "many rows per article" or "one representative category" semantics. The neighbouring functions in the same file already use Option A on `category_members`, so the convention is established.

### P1 — `to_sql(..., "all_articles", ["article_id", "category"], ...)`

**Original code** (`src/td_core/copy_data/by_title/all_articles.py:85–87`):

```python
def start_to_sql(data):
    data2 = [{"article_id": title, "category": category}
             for title, category in data.items()]
    to_sql(data2, "all_articles", ["article_id", "category"],
           title_column="article_id")
```

The `to_sql` helper (`src/td_core/mdpyget/bots/to_sql.py:151`) selects existing rows, compares them by `title_column`, then INSERTs new ones and UPDATEs changed ones. It assumes `title_column` is unique — true for `all_articles.article_id`, **not** true for `category_members.article_id`.

**Rewritten equivalent using `category_members`:** the same script already populates `category_members` correctly via `add_category_members_to_sql()` (lines 50–65) using `insert_dict(data2, "category_members", ["article_id", "category"])`. That helper is `category_members`-shape-aware (no upsert-by-article_id assumption).

The right rewrite is therefore **deletion**, not substitution:

```python
def main():
    ...
    # start_to_sql(data)               # ← drop: redundant with category_members
    add_category_members_to_sql(to_add_category_members)
```

If a bridge period is required, write to both targets but keep `category_members` as the source of truth.

- **category nullable in writes?** No — `category` is always set from the source dict (`{title: cat}`), so the NOT NULL constraint on `category_members.category` is satisfied. Make sure all keys used here exist in `categories(category)` first (the FK), which they should because they come from `sql_for_mdwiki.get_db_categories()` (line 17).
- **Risk level: LOW.** The write is redundant; deleting it removes the only INSERT into `all_articles` in the entire audited surface.

### P2 — `get_all_from_table(table_name="all_articles")` in `fix_it_db.py`

**Original code** (`src/td_core/fix_user_pages/fix_it_db.py:15, 39–43`):

```python
all_infos = sql_for_mdwiki.get_all_from_table(table_name="all_articles")
all_infos = {x["article_id"]: x for x in all_infos}
...
data = all_infos.get(new["title"])
if data:
    new["word"] = (
        data.get("w_lead_words") if new.get("translate_type") == "lead"
        else data.get("w_all_words")
    )
```

The current schema of `all_articles` is `(id, article_id, category)` — there are no `w_lead_words` / `w_all_words` columns, so `data.get(...)` always returns `None`. The code path is silently broken; this is unrelated to the migration.

**Rewritten equivalent:** the data the code actually wants lives in the `words` table (`w_title`, `w_lead_words`, `w_all_words`). Either:

1. **Preferred — re-point at the right table:**
   ```python
   words_rows = sql_for_mdwiki.select_md_sql(
       "SELECT w_title, w_lead_words, w_all_words FROM words;",
       return_dict=True,
   )
   all_infos = {x["w_title"]: x for x in words_rows}
   ```
2. **Or — delete this branch.** `bot.py` already routes through `fix_it_db_new.py`, which does not consult `all_articles` at all. The `fix_it_db.py` module appears to be a legacy fallback.

If for any reason the article_id index is still desired, replace with:

```python
articles = sql_for_mdwiki.select_md_sql(
    "SELECT DISTINCT article_id FROM category_members;",
    return_dict=True,
)
all_infos = {x["article_id"]: x for x in articles}
```

(returns no `w_*` keys, but neither does the current code in practice).

- **Risk level: HIGH.** Not because the SQL change is hard, but because the surrounding logic is already broken and depends on columns that never existed in `all_articles`. Touching it without a behavioural intent is risky; the safe move is to deprecate `fix_it_db.py`.

### P3 / P4 / P5 — `select article_id from all_articles;`

**Original code** (`countref.py:62`, `countrefs_and_words.py:76`, `words.py:59` — identical):

```python
que = """select article_id from all_articles;"""
sq  = sql_for_mdwiki.select_md_sql(que, return_dict=True)
titles2 = [q["article_id"] for q in sq]
```

Used purely to get the working set of article titles for nightly metric jobs.

**Rewritten equivalent using `category_members`:**

```python
que = """select DISTINCT article_id from category_members;"""
```

`DISTINCT` is required because the same article can appear under multiple categories.

- **category nullable in writes?** N/A — read-only.
- **Risk level: LOW.** Pure mechanical substitution.

---

## 4. Blocking Issues

| # | Issue | Affected query | Severity |
|---|-------|----------------|----------|
| B1 | Joins assume `article_id` is globally unique. `category_members` allows the same `article_id` under N categories, which inflates result rows. | T1, D1 | Medium — pick `DISTINCT` / `GROUP BY` / "1 representative category" subquery |
| B2 | Lookup tables (Python dicts keyed on `article_id`) assume one row per article. | P2, P3, P4, P5 | Low — `SELECT DISTINCT article_id` is enough; `dict[article_id] = …` then loses any multi-category context, which is acceptable for these read paths |
| B3 | INSERT into `all_articles` uses `to_sql` upsert keyed on `article_id`. Replacing the table with `category_members` requires honouring the `(category, article_id)` composite key (already correctly handled by `insert_dict` in the same script). | P1 | Low — drop the redundant `to_sql` call |
| B4 | `fix_it_db.py` reads `w_lead_words` / `w_all_words` from `all_articles`, columns that do not exist in the current schema. Pre-existing latent bug. | P2 | High for that code path — needs a real refactor (point at `words`) or deletion, not substitution |
| B5 | `category_members.category` is `NOT NULL` and FK-bound to `categories(category)`. Any code path that today writes a NULL category into `all_articles` would fail against `category_members`. | (None observed in audited code) | None today — but the table allows it, so future writers must validate |
| B6 | `refactor.md:845` plans a query against the deleted **view** `all_articles_titles`. Different object, but worth removing/rewriting in the same pass to avoid confusion. | — | Cosmetic |
| B7 | No live `FOREIGN KEY` references `all_articles` in `sql.sql` of either PHP repo. The historical `all_exists.article_id → all_articles.article_id` FK is documented as already removed. | — | None |

---

## 5. Recommendations

### 5.1 Verdict
`all_articles` **can be fully dropped** after the seven changes below land. There is no live FK pointing at it, and every read/write path has a clear `category_members` equivalent.

### 5.2 Suggested sequence (ordered by risk)

**Phase 1 — LOW risk, mechanical (do these first):**
1. **mdwiki-python-files / countref.py, countrefs_and_words.py, words.py** — change the three `select article_id from all_articles;` strings to `select DISTINCT article_id from category_members;`. (P3, P4, P5)
2. **mdwiki-python-files / `copy_data/by_title/all_articles.py`** — remove the `start_to_sql(data)` call (or stop writing to `all_articles`); keep the existing `add_category_members_to_sql(...)` call which is the source of truth. (P1)

**Phase 2 — MEDIUM risk, semantic decision:**

3. **TD_API / `src/api_cod/subs/missing_exists.php` (`exists_by_qids_query`)** — replace the `LEFT JOIN all_articles aa ON aa.article_id = q.title` with one of the two patterns in §3 / T1. Recommended: Option A (filter via `cm.category`/campaign), matching the convention already used by the neighbouring functions in the same file. (T1)
4. **Translation-Dashboard / `src/backend/api_or_sql/new_sql_tables.php` (`exists_by_qids_query`)** — apply the identical rewrite as in step 3. (D1)

**Phase 3 — HIGH risk, requires a decision:**

5. **mdwiki-python-files / `fix_user_pages/fix_it_db.py`** — either:
    - delete the module and route everything through `fix_it_db_new.py` (which already ignores `all_articles`), or
    - re-point `get_all_from_table` at `words` so the `w_lead_words` / `w_all_words` lookup actually works. (P2)

**Phase 4 — schema cleanup (only after Phases 1–3 are deployed and observed):**

6. Drop the table:
    ```sql
    DROP TABLE all_articles;
    ```
   And remove the `CREATE TABLE all_articles (…)` block from:
    - `TD_API/sql.sql` (lines 33–40)
    - `Translation-Dashboard/sql.sql` (lines 32–39)
7. Update documentation:
    - In `TD_API/docs/sql_tables_deleted.md`, move the `all_articles` entry from the "[ ]" (still live) bucket to the deleted bucket and add the rewrite recipes from §3.
    - In `TD_API/refactor.md` line 845, replace the reference to the deleted view `all_articles_titles` with the equivalent direct query against `category_members` + `qids`.

### 5.3 Optional interim safety net

If staging cannot be cut over atomically, create a **read-side compatibility view** to keep `all_articles` shape available without the underlying table:

```sql
CREATE OR REPLACE VIEW all_articles AS
SELECT
    MIN(id)         AS id,         -- arbitrary, kept for shape compat only
    article_id,
    MIN(category)   AS category    -- one representative category per article
FROM category_members
GROUP BY article_id;
```

This gives every legacy read query (T1, D1, P2, P3, P4, P5) a working `all_articles` to query while you ship Phases 1–3, then drop the view in Phase 4. **Do not** create this view if any new write path could attempt to INSERT into `all_articles` — views with `GROUP BY` are not updatable.

### 5.4 Test surface to exercise after each phase

- `GET /api.php?get=exists_by_qids&lang=<x>&category=<y>` (TD_API; covers T1)
- `GET /api.php?get=exists_by_qids&lang=<x>&campaign=<y>` (TD_API; covers T1's campaign branch)
- The Translation-Dashboard wrapper that calls `exists_by_qids_query` (covers D1)
- `python3 core8/pwb.py td_core/mdcount/countref sql` (covers P3)
- `python3 core8/pwb.py td_core/mdcount/words sql` (covers P5)
- `python3 core8/pwb.py td_core/mdcount/countrefs_and_words sql` (covers P4)
- A dry run of `td_core/copy_data/by_title/all_articles` confirming `category_members` row counts are unchanged (covers P1)
