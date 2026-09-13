# Cart locations — `cart_locations`

[Database schema index](README.md)

One latest GPS position per cart.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `cart_id` | `bigint unsigned` | No | None | — |
| `latitude` | `decimal(10,7)` | No | None | — |
| `longitude` | `decimal(10,7)` | No | None | — |
| `address` | `varchar(255)` | Yes | NULL | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |
| `recorded_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `cart_locations_cart_id_unique` | UNIQUE | `cart_id` |
| `cart_locations_updated_at_index` | BTREE | `updated_at` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `cart_locations_cart_id_foreign` | `cart_id` | [carts](carts.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

Unique cart_id enforces one current location. Coordinates are validated in the API; no database spatial index or latitude/longitude CHECK constraint exists. updated_at determines location freshness.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `cart_locations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `recorded_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_locations_cart_id_unique` (`cart_id`),
  KEY `cart_locations_updated_at_index` (`updated_at`),
  CONSTRAINT `cart_locations_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Current-position retention decision

Keep one row per cart, enforced by UNIQUE(cart_id). Every GPS submission replaces the coordinates in that row; repeated identical submissions refresh the timestamps. No GPS history row or cart activity event is inserted automatically.

`recorded_at` is the server receipt time of the latest location-record save, not a device-supplied GPS capture time. `updated_at` remains the row modification time and the existing indexed freshness field. Both refresh through the location API and Eloquent saves, including dashboard location saves. Direct SQL updates bypass model timestamp behavior.

The added column is nullable for legacy records. The migration backfills it from updated_at, falling back to created_at, without making old positions appear newly recorded. New Eloquent saves populate it automatically. Client-supplied recorded_at/updated_at values are not accepted by the location endpoint.
