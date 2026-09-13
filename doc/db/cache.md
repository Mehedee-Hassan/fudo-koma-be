# Database cache — `cache`

[Database schema index](README.md)

Laravel cache entries when CACHE_STORE=database.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `key` | `varchar(255)` | No | None | — |
| `value` | `mediumtext` | No | None | — |
| `expiration` | `bigint` | No | None | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `cache_expiration_index` | BTREE | `expiration` |
| `primary` | PRIMARY KEY | `key` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

expiration is an epoch time. Switching to Redis moves cache usage away from this table; it does not migrate notification records.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
