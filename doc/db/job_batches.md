# Job batches — `job_batches`

[Database schema index](README.md)

Standard Laravel job batch tracking.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `varchar(255)` | No | None | — |
| `name` | `varchar(255)` | No | None | — |
| `total_jobs` | `int` | No | None | — |
| `pending_jobs` | `int` | No | None | — |
| `failed_jobs` | `int` | No | None | — |
| `failed_job_ids` | `longtext` | No | None | — |
| `options` | `mediumtext` | Yes | NULL | — |
| `cancelled_at` | `int` | Yes | NULL | — |
| `created_at` | `int` | No | None | — |
| `finished_at` | `int` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

No custom batch workflow currently uses this table. Time columns contain epoch values; failed_job_ids/options contain serialized framework data.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
