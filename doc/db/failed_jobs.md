# Failed queue jobs — `failed_jobs`

[Database schema index](README.md)

Failures from the standard Laravel queue.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `uuid` | `varchar(255)` | No | None | — |
| `connection` | `varchar(255)` | No | None | — |
| `queue` | `varchar(255)` | No | None | — |
| `payload` | `longtext` | No | None | — |
| `exception` | `longtext` | No | None | — |
| `failed_at` | `timestamp` | No | `CURRENT_TIMESTAMP` | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `failed_jobs_connection_queue_failed_at_index` | BTREE | `connection`, `queue`, `failed_at` |
| `failed_jobs_uuid_unique` | UNIQUE | `uuid` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

This table is separate from failed FCM deliveries in push_deliveries. failed_at defaults to the current database timestamp.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
