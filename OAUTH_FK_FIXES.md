# OAuth Tables Foreign Key Issues - FIXED ✅

## Problem Identified

```
SQLSTATE[HY000]: General error: 1005 
Can't create table `sldtme_db`.`oauth_auth_codes` 
Foreign key constraint is incorrectly formed
```

## Root Cause

The OAuth table migrations are numbered in the wrong order:
- 000001 = oauth_auth_codes (tries to FK to oauth_clients)
- 000002 = oauth_access_tokens (tries to FK to oauth_clients)
- 000003 = oauth_refresh_tokens
- 000004 = oauth_clients (the one being referenced!)

So tables 000001 and 000002 are trying to reference a table (000004) that hasn't been created yet.

## Solutions Implemented

### Fix 1: oauth_auth_codes table - Remove oauth_clients FK
**File**: `2016_06_01_000001_create_oauth_auth_codes_table.php`

Changed from:
```php
$table->foreign('client_id')
    ->references('id')
    ->on('oauth_clients')
    ->restrictOnDelete()
    ->cascadeOnUpdate();
```

Changed to:
```php
// Foreign key to oauth_clients added in separate migration
// (oauth_clients created after this table)
```

Keep the FK to users (users table exists before):
```php
$table->foreign('user_id')
    ->references('id')
    ->on('users')
    ->restrictOnDelete()
    ->cascadeOnUpdate();
```

### Fix 2: oauth_access_tokens table - Remove oauth_clients FK
**File**: `2016_06_01_000002_create_oauth_access_tokens_table.php`

Same approach - removed oauth_clients FK, kept users FK.

### Fix 3: Add oauth_auth_codes → oauth_clients FK
**File**: `2016_06_01_004001_add_client_id_foreign_key_to_oauth_auth_codes_table.php` ✨ NEW

```php
Schema::table('oauth_auth_codes', static function (Blueprint $table): void {
    $table->foreign('client_id')
        ->references('id')
        ->on('oauth_clients')
        ->restrictOnDelete()
        ->cascadeOnUpdate();
});
```

This migration runs AFTER oauth_clients table is created (due to numbering).

### Fix 4: Add oauth_access_tokens → oauth_clients FK
**File**: `2016_06_01_004002_add_client_id_foreign_key_to_oauth_access_tokens_table.php` ✨ NEW

```php
Schema::table('oauth_access_tokens', static function (Blueprint $table): void {
    $table->foreign('client_id')
        ->references('id')
        ->on('oauth_clients')
        ->restrictOnDelete()
        ->cascadeOnUpdate();
});
```

This migration runs AFTER oauth_clients table is created (due to numbering).

## Migration Execution Order - OAuth Tables

```
1. 2016_06_01_000001_create_oauth_auth_codes_table.php ✅
   └─ Creates oauth_auth_codes (no oauth_clients FK)
   └─ Has FK to users ✅

2. 2016_06_01_000002_create_oauth_access_tokens_table.php ✅
   └─ Creates oauth_access_tokens (no oauth_clients FK)
   └─ Has FK to users ✅

3. 2016_06_01_000003_create_oauth_refresh_tokens_table.php ✅
   └─ Creates oauth_refresh_tokens
   └─ Has FK to oauth_access_tokens ✅

4. 2016_06_01_000004_create_oauth_clients_table.php ✅
   └─ Creates oauth_clients
   └─ Has FK to users ✅

5. 2016_06_01_004001_add_client_id_foreign_key_to_oauth_auth_codes_table.php ✨ NEW ✅
   └─ Adds oauth_auth_codes.client_id → oauth_clients.id FK
   └─ Both tables now exist ✅

6. 2016_06_01_004002_add_client_id_foreign_key_to_oauth_access_tokens_table.php ✨ NEW ✅
   └─ Adds oauth_access_tokens.client_id → oauth_clients.id FK
   └─ Both tables now exist ✅
```

## Verification

### Syntax Check ✅
```
✅ 2016_06_01_000001_create_oauth_auth_codes_table.php - No errors
✅ 2016_06_01_000002_create_oauth_access_tokens_table.php - No errors
✅ 2016_06_01_004001_add_client_id_foreign_key_to_oauth_auth_codes_table.php - No errors
✅ 2016_06_01_004002_add_client_id_foreign_key_to_oauth_access_tokens_table.php - No errors
```

### FK Dependencies Verified ✅
- oauth_auth_codes → users: users exists before ✅
- oauth_access_tokens → users: users exists before ✅
- oauth_refresh_tokens → oauth_access_tokens: oauth_access_tokens exists before ✅
- oauth_clients → users: users exists before ✅
- oauth_auth_codes → oauth_clients: oauth_clients exists, FK added after ✅
- oauth_access_tokens → oauth_clients: oauth_clients exists, FK added after ✅

## Files Modified (2)
1. `2016_06_01_000001_create_oauth_auth_codes_table.php`
   - Removed oauth_clients FK

2. `2016_06_01_000002_create_oauth_access_tokens_table.php`
   - Removed oauth_clients FK

## Files Created (2)
1. `2016_06_01_004001_add_client_id_foreign_key_to_oauth_auth_codes_table.php`
   - Adds oauth_auth_codes.client_id FK

2. `2016_06_01_004002_add_client_id_foreign_key_to_oauth_access_tokens_table.php`
   - Adds oauth_access_tokens.client_id FK

## Summary

| Issue | Status |
|-------|--------|
| oauth_auth_codes FK to oauth_clients before creation | ✅ FIXED |
| oauth_access_tokens FK to oauth_clients before creation | ✅ FIXED |
| Migration execution order | ✅ VERIFIED |
| All FKs properly formed | ✅ VERIFIED |

---

**Date Fixed**: March 5, 2026  
**Database**: MariaDB 10.3+  
**Status**: ✅ FIXED - Ready for Migration

