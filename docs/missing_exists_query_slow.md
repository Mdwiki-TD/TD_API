# query slow need to be optimized

```sql
SELECT
    la.code AS language_code,
    la.autonym AS autonym,
    la.name AS language_name,
    count(*) AS total,
    SUM( CASE WHEN aq.target IS NULL THEN 1 ELSE 0 END ) AS missing_title_count,
    SUM( CASE WHEN aq.target IS NOT NULL THEN 1 ELSE 0 END ) AS available_title_count
FROM
    category_members c
    JOIN langs la
    LEFT JOIN qids q ON q.title = c.article_id
    LEFT JOIN all_qids_exists aq ON aq.qid = q.qid
    AND la.code = aq.code
WHERE
    c.category = ?
GROUP BY 1, 2, 3
ORDER BY 4 ASC;
```

# database table:

```sql
CREATE TABLE
    category_members (
        id int NOT NULL AUTO_INCREMENT,
        category varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
        article_id varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY category_article_id (category, article_id),
        KEY article_id (article_id),
        CONSTRAINT category_members_ibfk_1 FOREIGN KEY (category) REFERENCES categories (category)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    langs (
        lang_id int NOT NULL AUTO_INCREMENT,
        code varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
        autonym varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
        name varchar(70) COLLATE utf8mb4_unicode_ci NOT NULL,
        redirects longtext COLLATE utf8mb4_unicode_ci,
        PRIMARY KEY (lang_id),
        CONSTRAINT langs_chk_1 CHECK (json_valid (redirects))
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    qids (
        id int unsigned NOT NULL AUTO_INCREMENT,
        title varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
        qid varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY title_qid (title, qid),
        KEY idx_qids_title (title)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

CREATE TABLE
    all_qids_exists (
        id int NOT NULL AUTO_INCREMENT,
        qid varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        code varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
        target varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY qid_code (qid, code),
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

```
