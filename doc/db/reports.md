# Moderation reports — `reports`

[Database schema index](README.md)

User-submitted reports and administrator resolutions.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `reporter_id` | `bigint unsigned` | No | None | — |
| `target_type` | `varchar(255)` | No | None | — |
| `target_id` | `bigint unsigned` | No | None | — |
| `reason` | `varchar(255)` | No | None | — |
| `note` | `text` | Yes | NULL | — |
| `status` | `varchar(255)` | No | `open` | — |
| `resolution_note` | `text` | Yes | NULL | — |
| `resolved_by` | `bigint unsigned` | Yes | NULL | — |
| `resolved_at` | `timestamp` | Yes | NULL | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `id` |
| `reports_reporter_id_foreign` | BTREE | `reporter_id` |
| `reports_resolved_by_foreign` | BTREE | `resolved_by` |
| `reports_status_index` | BTREE | `status` |

## Foreign keys

| Constraint | Local columns | References | On delete | On update |
|---|---|---|---|---|
| `reports_reporter_id_foreign` | `reporter_id` | [users](users.md) (`id`) | CASCADE | NO ACTION |
| `reports_resolved_by_foreign` | `resolved_by` | [users](users.md) (`id`) | SET NULL | NO ACTION |

## Behavior and constraints

target_type is cart/user; target_id is an application-validated polymorphic reference without a foreign key. Deleting the reported target can leave the report in place. status uses open/reviewing/resolved/dismissed. resolved_by records the acting admin and becomes NULL when that account is deleted.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reporter_id` bigint unsigned NOT NULL,
  `target_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` bigint unsigned NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `resolution_note` text COLLATE utf8mb4_unicode_ci,
  `resolved_by` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_reporter_id_foreign` (`reporter_id`),
  KEY `reports_resolved_by_foreign` (`resolved_by`),
  KEY `reports_status_index` (`status`),
  CONSTRAINT `reports_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
