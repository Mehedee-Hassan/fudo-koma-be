# Platform settings — `settings`

[Database schema index](README.md)

Application-wide key/value configuration.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `key` | `varchar(255)` | No | None | — |
| `value` | `text` | No | None | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `key` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

Current keys are push_enabled and location_max_age_minutes. Values are stored as text and converted by application code. Database credentials and Firebase service-account secrets are not stored here.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `settings` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
