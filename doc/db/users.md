# Accounts — `users`

[Database schema index](README.md)

Customer, owner, and administrator accounts.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `id` | `bigint unsigned` | No | None | AUTO_INCREMENT |
| `name` | `varchar(255)` | No | None | — |
| `email` | `varchar(255)` | No | None | — |
| `email_verified_at` | `timestamp` | Yes | NULL | — |
| `password` | `varchar(255)` | No | None | — |
| `remember_token` | `varchar(100)` | Yes | NULL | — |
| `created_at` | `timestamp` | Yes | NULL | — |
| `updated_at` | `timestamp` | Yes | NULL | — |
| `role` | `varchar(255)` | No | `customer` | — |
| `is_active` | `tinyint(1)` | No | `1` | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `id` |
| `users_email_unique` | UNIQUE | `email` |
| `users_role_index` | BTREE | `role` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

`role` uses customer/owner/admin in application validation. `is_active` controls account access. Passwords are hashed. Email verification fields exist, but verification and self-service password-reset workflows are not implemented.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
