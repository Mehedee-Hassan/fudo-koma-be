# Migration history — `migrations`

[Database schema index](README.md)

Laravel schema migration bookkeeping.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `int unsigned` | No | None | AUTO_INCREMENT |
| `migration` | `varchar(255)` | No | None | — |
| `batch` | `int` | No | None | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

migration stores the migration name; batch groups migrations applied together. This framework-managed table is created by the migration repository, not an application migration file.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
