# Notification inbox — `notifications`

[Database schema index](README.md)

Persistent per-user inbox messages.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `user_id` | `bigint unsigned` | No | None | — |
| `cart_id` | `bigint unsigned` | Yes | NULL | — |
| `dedupe_key` | `varchar(255)` | No | None | — |
| `type` | `varchar(255)` | No | None | — |
| `title` | `varchar(255)` | No | None | — |
| `body` | `text` | No | None | — |
| `read_at` | `timestamp` | Yes | NULL | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `notifications_cart_id_foreign` | BTREE | `cart_id` |
| `notifications_dedupe_key_unique` | UNIQUE | `dedupe_key` |
| `notifications_user_id_foreign` | BTREE | `user_id` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `notifications_cart_id_foreign` | `cart_id` | [carts](carts.md) (`id`) | CASCADE | NO ACTION |
| `notifications_user_id_foreign` | `user_id` | [users](users.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

type is update/nearby in the dispatcher. dedupe_key prevents duplicate generated notifications. read_at=NULL means unread. Nullable cart_id still cascades deletion when a referenced cart is removed.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `cart_id` bigint unsigned DEFAULT NULL,
  `dedupe_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notifications_dedupe_key_unique` (`dedupe_key`),
  KEY `notifications_user_id_foreign` (`user_id`),
  KEY `notifications_cart_id_foreign` (`cart_id`),
  CONSTRAINT `notifications_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
