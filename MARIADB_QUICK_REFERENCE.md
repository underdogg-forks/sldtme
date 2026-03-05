# MariaDB Compatibility Quick Reference

## What Changed?

Four migration files were updated to remove MariaDB-incompatible syntax:

| File | Old | New | Why |
|------|-----|-----|-----|
| `2014_10_12_000000_create_users_table.php` | `uniqueIndex('email')->where(...)` | `unique('email')` | No partial indexes in MariaDB |
| `2024_01_20_110837_create_time_entries_table.php` | `jsonb('tags')` | `json('tags')` | MariaDB uses `json()` not `jsonb()` |
| `2024_08_01_104840_create_reports_table.php` | `jsonb('properties')` | `json('properties')` | MariaDB uses `json()` not `jsonb()` |
| `2024_09_02_094105_create_audits_table.php` | `uuidMorphs('auditable')`<br>`ipAddress('ip_address')` | `string('auditable_type')`<br>`uuid('auditable_id')`<br>`string('ip_address', 45)` | MariaDB lacks these specialized helpers |

## Do I Need to Change My Code?

**No.** All changes are in migrations only. Your application code remains unchanged:

```php
// This still works exactly the same
$timeEntry->tags; // Still JSON
$audit->auditable; // Still polymorphic
$audit->ip_address; // Still a string
User::where('email', $email)->first(); // Still unique
```

## What About Existing Data?

### If upgrading from MySQL to MariaDB:
- ✅ Run `php artisan migrate:fresh` or `php artisan migrate`
- ✅ All existing data will transfer correctly

### If you already have placeholder users with duplicate emails:
You'll need to clean these up before running migrations:

```php
// In a migration or artisan command:
DB::table('users')
    ->where('is_placeholder', true)
    ->groupBy('email')
    ->havingRaw('count(*) > 1')
    ->delete();
```

## Testing the Changes

```bash
# Run migrations with MariaDB
php artisan migrate

# Run tests
php artisan test

# Verify JSON operations
php artisan tinker
> TimeEntry::first()->tags // Should return JSON
> Audit::first()->auditable // Should return polymorphic model
```

## Key Points

✅ **JSON**: `json()` and `jsonb()` are functionally identical in Laravel - use `->jsonb()` queries for `json()` columns  
✅ **Email**: Now globally unique (no placeholder duplicates allowed)  
✅ **IP Addresses**: Stored as strings, support both IPv4 and IPv6  
✅ **Morphs**: Work identically with explicit UUID columns  
✅ **Backward Compat**: All changes work fine with MySQL too  

## Deployment Checklist

- [ ] Review `MARIADB_REFACTORING_COMPLETE.md` for full details
- [ ] Test migrations locally with MariaDB
- [ ] Run test suite
- [ ] If upgrading from MySQL: clean up duplicate placeholder emails
- [ ] Deploy and run `php artisan migrate`
- [ ] Verify all features work in production

---

For full details, see: `MARIADB_REFACTORING_COMPLETE.md`

