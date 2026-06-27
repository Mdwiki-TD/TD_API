[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/Mdwiki-TD/TD_API)

# Mdwiki Translation Dashboard API (TD_API)

The API System is the central data access layer of MDwiki, providing standardized HTTP endpoints that allow components such as the Translation Dashboard and Wiki Management Tools to query the database. This page documents the architecture, endpoints, query building process, and usage patterns of the API system.

# Overview

The MDwiki API System is a simple but powerful API that accepts HTTP GET requests with parameters and returns data in JSON format. It serves as the primary interface between the database and the frontend applications, handling data retrieval for pages, users, statistics, and more.

# OpenAPI interface

The **OpenAPI** interface (formerly known as Swagger) provides an interactive and explorable documentation of all available endpoints in the **TD_API** system. It allows users and developers to understand how to interact with the API and test requests directly from the browser

## 📄 Interactive UI

You can access the OpenAPI interactive UI here:
👉 [https://mdwiki.toolforge.org/api/openapi.html](https://mdwiki.toolforge.org/api/openapi.html)

# End points

All endpoints accept **HTTP GET** requests. The API uses a single entry point (`/api.php`) with a `?get=` query parameter to specify the endpoint.

| Endpoint                                    | Method | Description                                                                                  |
| ------------------------------------------- | ------ | -------------------------------------------------------------------------------------------- |
| `/`                                         | GET    | Main entry                                                                                   |
| `/api.php?get=missing`                      | GET    | Articles missing translation in all languages for a category                                 |
| `/api.php?get=missing_by_lang_and_category` | GET    | Articles missing translation in a specific language + category                               |
| `/api.php?get=exists_statics_by_category`   | GET    | Statistics of articles that exist per language for a category                                |
| `/api.php?get=exists_by_lang_and_category`  | GET    | Articles that exist in a specific language + category                                        |
| `/api.php?get=statics_by_category`          | GET    | Count of existing articles grouped by language for a category                                |
| `/api.php?get=users`                        | GET    | List of usernames (supports `userlike` filter)                                               |
| `/api.php?get=category_members`             | GET    | Article IDs from a category membership table                                                 |
| `/api.php?get=revids`                       | GET    | MDWiki revision IDs for titles                                                               |
| `/api.php?get=titles`                       | GET    | Page titles with assessments, refs, views, QIDs, words                                       |
| `/api.php?get=pages_users_to_main`          | GET    | Join of pages_users_to_main with pages_users                                                 |
| `/api.php?get=coordinators`                 | GET    | List of coordinators with active status                                                      |
| `/api.php?get=leaderboard_table`            | GET    | Raw leaderboard data (pages, users, words, views)                                            |
| `/api.php?get=leaderboard_table_formated`   | GET    | Formatted leaderboard (by_lang, by_user, by_month)                                           |
| `/api.php?get=status`                       | GET    | Page publication counts by month (optional filters)                                          |
| `/api.php?get=views`                        | GET    | Page view stats joined with pages                                                            |
| `/api.php?get=views_new`                    | GET    | Page views from views_new_all table                                                          |
| `/api.php?get=user_access`                  | GET    | Access keys with usernames                                                                   |
| `/api.php?get=qids`                         | GET    | Wikidata QIDs (supports `dis` param)                                                         |
| `/api.php?get=qids_others`                  | GET    | Additional Wikidata QIDs                                                                     |
| `/api.php?get=count_pages`                  | GET    | Count of targets per user, ordered descending                                                |
| `/api.php?get=top_lang_of_users`            | GET    | Top language per user (by page count)                                                        |
| `/api.php?get=top_langs`                    | GET    | Top languages by targets, words, views                                                       |
| `/api.php?get=top_users`                    | GET    | Top users by targets, words, views                                                           |
| `/api.php?get=users_by_last_pupdate`        | GET    | Users with their latest page update                                                          |
| `/api.php?get=langs`                        | GET    | All languages with code, autonym, name, redirects                                            |
| `/api.php?get=user_views`                   | GET    | Page views filtered by a specific user                                                       |
| `/api.php?get=user_views2`                  | GET    | Page views filtered by a specific user (alias)                                               |
| `/api.php?get=language_settings`            | GET    | Language settings with distinct values                                                       |
| `/api.php?get=publish_reports_stats`        | GET    | Publication reports stats by year/month/lang/user/result                                     |
| `/api.php?get=publish_reports`              | GET    | Publication reports with selectable fields                                                   |
| `/api.php?get=lang_views`                   | GET    | Page views filtered by language                                                              |
| `/api.php?get=lang_views2`                  | GET    | Page views filtered by language (alias)                                                      |
| `/api.php?get=graph_data`                   | GET    | Monthly page publication counts                                                              |
| `/api.php?get=words`                        | GET    | Word counts for page titles (lead and all words)                                             |
| `/api.php?get=pages_by_user_or_lang`        | GET    | Pages filtered by user or language with views                                                |
| `/api.php?get=pages`                        | GET    | Pages with full details (title, word, translate_type, cat, lang, user, target, dates, views) |
| `/api.php?get=pages_users`                  | GET    | Pages from pages_users table                                                                 |
| `/api.php?get=pages_langs`                  | GET    | Languages used in the pages table                                                            |
| `/api.php?get=pages_users_langs`            | GET    | Languages used in the pages_users table                                                      |
| `/api.php?get=user_lang_status`             | GET    | User status by language (redirects to user_status)                                           |
| `/api.php?get=user_status`                  | GET    | User status by language with year/select options                                             |
| `/api.php?get=pages_with_views`             | GET    | Pages with views (redirects to pages)                                                        |
| `/api.php?get=in_process`                   | GET    | In-process translations with campaign and language info                                      |
| `/api.php?get=assessments`                  | GET    | Article assessments with importance                                                          |
| `/api.php?get=refs_counts`                  | GET    | Reference counts (lead and all) per title                                                    |
| `/api.php?get=enwiki_pageviews`             | GET    | English Wikipedia page views                                                                 |
| `/api.php?get=categories`                   | GET    | Available categories with campaign mappings                                                  |
| `/api.php?get=full_translators`             | GET    | Full translators with active status                                                          |
| `/api.php?get=users_no_inprocess`           | GET    | Users without in-process articles                                                            |
| `/api.php?get=projects`                     | GET    | Project groups                                                                               |
| `/api.php?get=settings`                     | GET    | System configuration settings                                                                |
| `/api.php?get=translate_type`               | GET    | Translation types (lead/full)                                                                |
