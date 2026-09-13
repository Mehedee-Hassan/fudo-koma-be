# Cart follows — `follows`

[Database schema index](README.md)

Persistent user/cart follow relationships.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `user_id` | `bigint unsigned` | No | None | — |
| `cart_id` | `bigint unsigned` | No | None | — |
| `is_following` | `tinyint(1)` | No | `1` | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `follows_cart_id_foreign` | BTREE | `cart_id` |
| `follows_user_id_cart_id_unique` | UNIQUE | `user_id`, `cart_id` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `follows_cart_id_foreign` | `cart_id` | [carts](carts.md) (`id`) | CASCADE | NO ACTION |
| `follows_user_id_foreign` | `user_id` | [users](users.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

Unique (user_id, cart_id) prevents duplicate relationships. Unfollow sets is_following=false. Owner ID is obtained through carts.owner_id rather than duplicated here.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `follows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `cart_id` bigint unsigned NOT NULL,
  `is_following` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `follows_user_id_cart_id_unique` (`user_id`,`cart_id`),
  KEY `follows_cart_id_foreign` (`cart_id`),
  CONSTRAINT `follows_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `follows_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
