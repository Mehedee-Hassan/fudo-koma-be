# Browser sessions — `sessions`

[Database schema index](README.md)

Database-backed administrator browser sessions.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `varchar(255)` | No | None | — |
| `user_id` | `bigint unsigned` | Yes | NULL | — |
| `ip_address` | `varchar(45)` | Yes | NULL | — |
| `user_agent` | `text` | Yes | NULL | — |
| `payload` | `longtext` | No | None | — |
| `last_activity` | `int` | No | None | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `id` |
| `sessions_last_activity_index` | BTREE | `last_activity` |
| `sessions_user_id_index` | BTREE | `user_id` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

user_id is indexed but has no database foreign key. last_activity is an epoch time used for session expiry. The .env SESSION_LIFETIME setting controls session duration; it is not a column default.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
