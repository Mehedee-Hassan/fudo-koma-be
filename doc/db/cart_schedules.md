# Serving schedules — `cart_schedules`

[Database schema index](README.md)

Weekly serving windows and dated one-off stops.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `cart_id` | `bigint unsigned` | No | None | — |
| `day_of_week` | `tinyint unsigned` | No | None | — |
| `opens_at` | `time` | No | None | — |
| `closes_at` | `time` | No | None | — |
| `timezone` | `varchar(255)` | No | `Asia/Tokyo` | — |
| `address` | `varchar(255)` | Yes | NULL | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |
| `specific_date` | `date` | Yes | NULL | — |
| `is_active` | `tinyint(1)` | No | `1` | — |
| `latitude` | `decimal(10,7)` | Yes | NULL | — |
| `longitude` | `decimal(10,7)` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `cart_schedules_cart_id_day_of_week_index` | BTREE | `cart_id`, `day_of_week` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `cart_schedules_cart_id_foreign` | `cart_id` | [carts](carts.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

day_of_week uses Monday=1 through Sunday=7. specific_date=NULL represents a weekly entry. Times are interpreted in timezone; closing before opening permits overnight windows. The (cart_id, day_of_week) index is not unique. Upserts match cart/weekday/date in application code; concurrency can create duplicates.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `cart_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `day_of_week` tinyint unsigned NOT NULL,
  `opens_at` time NOT NULL,
  `closes_at` time NOT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Tokyo',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `specific_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_schedules_cart_id_day_of_week_index` (`cart_id`,`day_of_week`),
  CONSTRAINT `cart_schedules_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
