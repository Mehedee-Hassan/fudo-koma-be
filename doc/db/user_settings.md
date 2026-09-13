# User preferences — `user_settings`

[Database schema index](README.md)

Per-account notification and proximity preferences.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `user_id` | `bigint unsigned` | No | None | — |
| `push_enabled` | `tinyint(1)` | No | `1` | — |
| `nearby_enabled` | `tinyint(1)` | No | `1` | — |
| `updates_enabled` | `tinyint(1)` | No | `1` | — |
| `radius_meters` | `int unsigned` | No | `5000` | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `id` |
| `user_settings_user_id_unique` | UNIQUE | `user_id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `user_settings_user_id_foreign` | `user_id` | [users](users.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

Unique user_id enforces one settings row. Defaults enable push, nearby, and update notifications with a 5000-meter radius. API validation permits 100–50000 meters.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `user_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `push_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `nearby_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `updates_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `radius_meters` int unsigned NOT NULL DEFAULT '5000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_settings_user_id_unique` (`user_id`),
  CONSTRAINT `user_settings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
