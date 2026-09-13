# Push devices — `device_tokens`

[Database schema index](README.md)

Registered Firebase Messaging tokens for user devices.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `user_id` | `bigint unsigned` | No | None | — |
| `token` | `text` | No | None | — |
| `token_hash` | `varchar(64)` | No | None | — |
| `platform` | `varchar(255)` | No | None | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `device_tokens_token_hash_unique` | UNIQUE | `token_hash` |
| `device_tokens_user_id_foreign` | BTREE | `user_id` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `device_tokens_user_id_foreign` | `user_id` | [users](users.md) (`id`) | CASCADE | NO ACTION |

## Behavior and constraints

token contains the provider token and is hidden from API output. token_hash is a SHA-256 lookup/deduplication value. platform is android/ios/web in application validation. Re-registering a token can transfer it to a different signed-in account.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `device_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_tokens_token_hash_unique` (`token_hash`),
  KEY `device_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `device_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
