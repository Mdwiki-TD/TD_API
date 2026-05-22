-   [x] titles_infos

    ```sql
    CREATE VIEW
        titles_infos AS
    select
        ase.title AS title,
        ase.importance AS importance,
        rc.r_lead_refs AS r_lead_refs,
        rc.r_all_refs AS r_all_refs,
        ep.en_views AS en_views,
        w.w_lead_words AS w_lead_words,
        w.w_all_words AS w_all_words,
        q.qid AS qid
    from
        assessments ase
        left join enwiki_pageviews ep on ase.title = ep.title
        left join qids q on q.title = ase.title
        left join refs_counts rc on rc.r_title = ase.title
        left join words w on w.w_title = ase.title;
    ```

-   [x] users_list

    ```sql
    CREATE VIEW users_list AS
    select
        users.user_id AS user_id,
        users.username AS username,
        users.wiki AS wiki,
        users.user_group AS user_group,
        users.reg_date AS reg_date
    from
        users;
    ```

-   [x] all_articles_titles

    ```sql
    CREATE VIEW
        all_articles_titles AS
    select
        q.qid AS qid,
        aa.article_id AS title,
        aa.category AS category
    from
        all_articles aa
        left join qids q on aa.article_id = q.title;

    ```

-   [x] all_qids_titles

    ```sql

    CREATE VIEW
        all_qids_titles AS
    select
        qq.qid AS qid,
        q.title AS title,
        aa.category AS category
    from
        all_qids qq
        left join qids q onqq.qid = q.qid
        left join all_articles aa on aa.article_id = q.title;

    ```

-   [x] keys_new

    ```sql
        CREATE TABLE
            keys_new (
                id int NOT NULL AUTO_INCREMENT,
                u_n text COLLATE utf8mb4_unicode_ci NOT NULL,
                a_k text COLLATE utf8mb4_unicode_ci NOT NULL,
                a_s text COLLATE utf8mb4_unicode_ci NOT NULL,
                created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
    ```

-   [x] wddone

    ```sql
        CREATE TABLE
            wddone (
                id int unsigned NOT NULL AUTO_INCREMENT,
                mdtitle varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
                target varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
                lang varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
                user varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
                PRIMARY KEY (id),
                KEY idx_target (target)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
    ```

-   [x] all_qids

    ```sql
        CREATE TABLE
            `all_qids` (
                `qid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `id` int NOT NULL AUTO_INCREMENT,
                PRIMARY KEY (`id`),
                UNIQUE KEY `qid` (`qid`)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

    ```

-   [x] all_exists

    ```sql
        CREATE TABLE
        all_exists (
            id int NOT NULL AUTO_INCREMENT,
            article_id varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            code varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY article_id_code (article_id, code),
            CONSTRAINT all_exists_ibfk_1 FOREIGN KEY (article_id) REFERENCES all_articles (article_id)
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
    ```

-   [ ] all_articles

    ```sql
        CREATE TABLE
            all_articles (
                id int NOT NULL AUTO_INCREMENT,
                article_id varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                category varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY article_id (article_id)
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
    ```
