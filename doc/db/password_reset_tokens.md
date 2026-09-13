# Password reset storage — `password_reset_tokens`

[Database schema index](README.md)

Framework table reserved for password reset tokens.

## Columns

| Column | MySQL type | Nullable | Default | Extra |
|---|---|---|---|---|
| `email` | `varchar(255)` | No | None | — |
| `token` | `varchar(255)` | No | None | — |
| `created_at` | `timestamp` | Yes | NULL | — |

## Indexes

| Name | Kind | Columns (in order) |
|---|---|---|
| `primary` | PRIMARY KEY | `email` |

## Foreign keys

No database-enforced foreign keys.

## Behavior and constraints

Table availability does not mean a password reset API is implemented. email is a primary key but is not a foreign key to users.

## MySQL DDL

Schema-only reference; use Laravel migrations to manage the application database.

```sql
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
