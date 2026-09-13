# Cart activity — `cart_updates`

[Database schema index](README.md)

Published announcements and activity events.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `cart_id` | `bigint unsigned` | No | None | — |
| `title` | `varchar(255)` | No | None | — |
| `body` | `text` | No | None | — |
| `fanout_at` | `timestamp` | Yes | NULL | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |
| `type` | `varchar(255)` | No | `announcement` | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `cart_updates_cart_id_foreign` | BTREE | `cart_id` |
| `cart_updates_fanout_at_index` | BTREE | `fanout_at` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `cart_updates_cart_id_foreign` | `cart_id` | [carts](carts.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

type supports announcement/opened/closed/moved/scheduleChanged/photoAdded in application validation. fanout_at records completed notification generation, not successful push delivery.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `cart_updates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fanout_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'announcement',
  PRIMARY KEY (`id`),
  KEY `cart_updates_cart_id_foreign` (`cart_id`),
  KEY `cart_updates_fanout_at_index` (`fanout_at`),
  CONSTRAINT `cart_updates_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
