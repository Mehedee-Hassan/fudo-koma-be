# Queued jobs — `jobs`

[Database schema index](README.md)

Standard Laravel database queue jobs.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `queue` | `varchar(255)` | No | None | — |
| `payload` | `longtext` | No | None | — |
| `attempts` | `smallint unsigned` | No | None | — |
| `reserved_at` | `int unsigned` | Yes | NULL | — |
| `available_at` | `int unsigned` | No | None | — |
| `created_at` | `int unsigned` | No | None | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `jobs_queue_index` | BTREE | `queue` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

Time columns contain epoch values. The current notification dispatcher uses push_deliveries directly; a separate jobs worker is not required for that flow.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
