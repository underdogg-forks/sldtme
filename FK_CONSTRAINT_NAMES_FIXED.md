# ✅ Foreign Key Constraint Names Fixed

## Issue Fixed

Added explicit foreign key constraint names to both FK migrations to avoid naming conflicts.

## Changes Made

### Migration 1: organizations.user_id FK
**File**: `2020_05_21_150000_add_user_id_foreign_key_to_organizations_table.php`

Added constraint name:
```php
$table->foreign('user_id', 'organizations_user_id_foreign')
```

Updated down() to use the constraint name:
```php
$table->dropForeign('organizations_user_id_foreign');
```

### Migration 2: users.current_team_id FK
**File**: `2020_05_21_250000_add_current_team_id_foreign_key_to_users_table.php`

Added constraint name:
```php
$table->foreign('current_team_id', 'users_current_team_id_foreign')
```

Updated down() to use the constraint name:
```php
$table->dropForeign('users_current_team_id_foreign');
```

## Why This Is Correct

When adding a foreign key to an **existing table**, use:
```php
$table->foreign('column_name', 'constraint_name')
```

When **removing** the FK, use:
```php
$table->dropForeign('constraint_name');
```

NOT `['column_name']` - use the actual constraint name!

## Complete Foreign Key Summary

| Table | Column | References | Constraint Name | Status |
|-------|--------|-----------|-----------------|--------|
| organizations | user_id | users.id | organizations_user_id_foreign | ✅ FIXED |
| users | current_team_id | organizations.id | users_current_team_id_foreign | ✅ FIXED |

## Migration Execution Order

1. ✅ users table created (no FKs)
2. ✅ organizations table created (no FKs)
3. ✅ Add organizations.user_id → users.id FK
4. ✅ Add users.current_team_id → organizations.id FK
5. ✅ All other tables...

---

**Status**: ✅ **READY TO DEPLOY**

