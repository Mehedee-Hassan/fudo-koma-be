# Cache locks — `cache_locks`

[Database schema index](README.md)

Shared locks when using the database cache store.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `key` | `varchar(255)` | No | None | — |
| `owner` | `varchar(255)` | No | None | — |
| `expiration` | `bigint` | No | None | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `cache_locks_expiration_index` | BTREE | `expiration` |
| `primary` | PRIMARY KEY | `key` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

Used to prevent overlapping dispatchers. owner identifies the lock holder; expiration is an epoch time.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
