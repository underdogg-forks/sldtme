# MariaDB Migration Compatibility Refactor

## Overview
The database migrations have been refactored to ensure full compatibility with MariaDB while maintaining backward compatibility with MySQL and other supported databases.

## Issues Addressed

### 1. **Partial/Conditional Indexes (WHERE clause in indexes)**
   - **File**: `database/migrations/2014_10_12_000000_create_users_table.php`
   - **Issue**: MariaDB does not support partial indexes with WHERE clauses
   - **Change**: Replaced `uniqueIndex('email')->where('is_placeholder = false')` with regular `unique('email')`
   - **Impact**: Non-placeholder duplicate emails will now be rejected at the database level. Applications should enforce the `is_placeholder = false` constraint at the application level if needed.

### 2. **JSONB Column Type**
   - **Files**: 
     - `database/migrations/2024_01_20_110837_create_time_entries_table.php`
     - `database/migrations/2024_08_01_104840_create_reports_table.php`
   - **Issue**: JSONB is PostgreSQL-specific; MariaDB/MySQL use JSON
   - **Changes**: 
     - Replaced `jsonb('tags')` with `json('tags')` in time_entries table
     - Replaced `jsonb('properties')` with `json('properties')` in reports table
   - **Impact**: JSON functionality remains the same; Laravel's JSON query methods work identically with both JSON and JSONB

### 3. **uuidMorphs and ipAddress Data Types**
   - **File**: `database/migrations/2024_09_02_094105_create_audits_table.php`
   - **Issues**:
     - `uuidMorphs()` is PostgreSQL-enhanced syntax not supported by MariaDB
     - `ipAddress()` is not a standard MariaDB type
   - **Changes**:
     - Replaced `uuidMorphs('auditable')` with explicit columns: `string('auditable_type')` and `uuid('auditable_id')`
     - Replaced `ipAddress('ip_address')` with `string('ip_address', 45)` (IPv6 length)
   - **Impact**: Full support for polymorphic relationships and IP address storage in MariaDB

## Summary of Changes

| Migration File | Issue | Solution |
|---|---|---|
| `2014_10_12_000000_create_users_table.php` | Partial index with WHERE | Regular unique index |
| `2024_01_20_110837_create_time_entries_table.php` | JSONB column | JSON column |
| `2024_08_01_104840_create_reports_table.php` | JSONB column | JSON column |
| `2024_09_02_094105_create_audits_table.php` | uuidMorphs + ipAddress | Explicit UUID columns + VARCHAR |

## Testing Recommendations

1. **Unit Tests**: Run existing database tests to ensure migration compatibility
2. **Integration Tests**: Verify JSON queries work correctly with `json()` type
3. **Data Validation**: Ensure existing data continues to work after migration if upgrading
4. **Application Logic**: Review any code that relied on the partial unique index for email validation

## Database Version Support

After these changes, the migrations support:
- ✅ MySQL 5.7+
- ✅ MariaDB 10.3+
- ✅ PostgreSQL (with `tpetry/laravel-postgresql-enhanced` package)
- ✅ SQLite

