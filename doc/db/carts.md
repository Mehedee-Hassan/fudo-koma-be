# Food carts — `carts`

[Database schema index](README.md)

Cart profiles and ownership.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `owner_id` | `bigint unsigned` | No | None | — |
| `name` | `varchar(255)` | No | None | — |
| `description` | `text` | Yes | NULL | — |
| `cuisine` | `varchar(255)` | Yes | NULL | — |
| `status` | `varchar(255)` | No | `closed` | — |
| `moderation_status` | `varchar(255)` | No | `pending` | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |
| `is_featured` | `tinyint(1)` | No | `0` | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `carts_moderation_status_index` | BTREE | `moderation_status` |
| `carts_owner_id_foreign` | BTREE | `owner_id` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `carts_owner_id_foreign` | `owner_id` | [users](users.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

Application values: status=open/closed; moderation_status=pending/approved/rejected. is_featured is administrator-controlled. These are VARCHAR/boolean columns, not SQL enum constraints.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cuisine` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'closed',
  `moderation_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `carts_owner_id_foreign` (`owner_id`),
  KEY `carts_moderation_status_index` (`moderation_status`),
  CONSTRAINT `carts_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
