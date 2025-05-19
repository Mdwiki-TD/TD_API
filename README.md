[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/Mdwiki-TD/TD_API)

# Mdwiki Translation Dashboard API (TD_API)

The API System is the central data access layer of MDwiki, providing standardized HTTP endpoints that allow components such as the Translation Dashboard and Wiki Management Tools to query the database. This page documents the architecture, endpoints, query building process, and usage patterns of the API system.

# Overview

The MDwiki API System is a simple but powerful API that accepts HTTP GET requests with parameters and returns data in JSON format. It serves as the primary interface between the database and the frontend applications, handling data retrieval for pages, users, statistics, and more.

# OpenAPI interface

The **OpenAPI** interface (formerly known as Swagger) provides an interactive and explorable documentation of all available endpoints in the **TD_API** system. It allows users and developers to understand how to interact with the API and test requests directly from the browser

## 📄 Interactive UI

You can access the OpenAPI interactive UI here:
👉 [https://mdwiki.toolforge.org/api/test.html](https://mdwiki.toolforge.org/api)
