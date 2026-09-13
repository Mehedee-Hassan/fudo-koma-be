# API tokens — `personal_access_tokens`

[Database schema index](README.md)

Laravel Sanctum bearer token records.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `tokenable_type` | `varchar(255)` | No | None | — |
| `tokenable_id` | `bigint unsigned` | No | None | — |
| `name` | `text` | No | None | — |
| `token` | `varchar(64)` | No | None | — |
| `abilities` | `text` | Yes | NULL | — |
| `last_used_at` | `timestamp` | Yes | NULL | — |
| `expires_at` | `timestamp` | Yes | NULL | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `personal_access_tokens_expires_at_index` | BTREE | `expires_at` |
| `personal_access_tokens_token_unique` | UNIQUE | `token` |
| `personal_access_tokens_tokenable_type_tokenable_id_index` | BTREE | `tokenable_type`, `tokenable_id` |
| `primary` | PRIMARY KEY | `id` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

token stores the SHA-256 hash of a bearer token. tokenable_type/tokenable_id is a polymorphic model reference with a composite index, not a database foreign key. App-issued tokens expire after 30 days; the scheduler prunes expired tokens.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
