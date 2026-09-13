# Push delivery attempts — `push_deliveries`

[Database schema index](README.md)

Separate delivery and retry state for each notification/device pair.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `notification_id` | `bigint unsigned` | No | None | — |
| `device_token_id` | `bigint unsigned` | No | None | — |
| `status` | `varchar(255)` | No | `pending` | — |
| `attempts` | `int unsigned` | No | `0` | — |
| `available_at` | `timestamp` | Yes | NULL | — |
| `last_error` | `text` | Yes | NULL | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `id` |
| `push_deliveries_available_at_index` | BTREE | `available_at` |
| `push_deliveries_device_token_id_foreign` | BTREE | `device_token_id` |
| `push_deliveries_notification_id_device_token_id_unique` | UNIQUE | `notification_id`, `device_token_id` |
| `push_deliveries_status_index` | BTREE | `status` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `push_deliveries_device_token_id_foreign` | `device_token_id` | [device_tokens](device_tokens.md) (`id`) | CASCADE | NO ACTION |
| `push_deliveries_notification_id_foreign` | `notification_id` | [notifications](notifications.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

status uses pending/sent/failed/skipped. attempts counts send attempts; available_at schedules retries. A unique notification_id/device_token_id pair prevents duplicate delivery records. At-least-once transport can still duplicate an external send after a crash.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `push_deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notification_id` bigint unsigned NOT NULL,
  `device_token_id` bigint unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attempts` int unsigned NOT NULL DEFAULT '0',
  `available_at` timestamp NULL DEFAULT NULL,
  `last_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `push_deliveries_notification_id_device_token_id_unique` (`notification_id`,`device_token_id`),
  KEY `push_deliveries_device_token_id_foreign` (`device_token_id`),
  KEY `push_deliveries_status_index` (`status`),
  KEY `push_deliveries_available_at_index` (`available_at`),
  CONSTRAINT `push_deliveries_device_token_id_foreign` FOREIGN KEY (`device_token_id`) REFERENCES `device_tokens` (`id`) ON DELETE CASCADE,
  CONSTRAINT `push_deliveries_notification_id_foreign` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
