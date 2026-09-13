# User locations — `user_locations`

[Database schema index](README.md)

One latest GPS position per customer.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `user_id` | `bigint unsigned` | No | None | — |
| `latitude` | `decimal(10,7)` | No | None | — |
| `longitude` | `decimal(10,7)` | No | None | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `id` |
| `user_locations_updated_at_index` | BTREE | `updated_at` |
| `user_locations_user_id_unique` | UNIQUE | `user_id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `user_locations_user_id_foreign` | `user_id` | [users](users.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

Unique user_id enforces one current position. The scheduler deletes user locations older than 24 hours; no location history is stored.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `user_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_locations_user_id_unique` (`user_id`),
  KEY `user_locations_updated_at_index` (`updated_at`),
  CONSTRAINT `user_locations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
