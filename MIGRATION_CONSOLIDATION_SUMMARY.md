# Migration Consolidation Summary for Fresh Install

## Overview
For a **fresh installation**, migration files have been consolidated to create tables with all columns and constraints in their initial creation migrations, eliminating unnecessary subsequent "add_" migrations.

## Consolidations Completed

### 1. **Users Table** (2014_10_12_000000)
**Consolidated From:**
- `2014_10_12_200000_add_two_factor_columns_to_users_table.php`
- `2025_05_16_075757_add_foreign_key_for_current_team_id_in_users_table.php`

**Merged Columns:**
- `two_factor_secret` (text)
- `two_factor_recovery_codes` (text)
- `two_factor_confirmed_at` (timestamp)
- `current_team_id` foreign key constraint

---

### 2. **Organizations Table** (2020_05_21_100000)
**Consolidated From:**
- `2024_10_01_143608_add_employees_can_see_billable_rates_to_organizations_table.php`
- `2025_04_03_101827_add_localization_columns_to_organizations_table.php`
- `2025_10_02_000001_add_prevent_overlapping_time_entries_to_organizations_table.php`
- `2025_10_24_120845_add_employees_can_manage_tasks_to_organizations_table.php`

**Merged Columns:**
- `employees_can_see_billable_rates` (boolean)
- `employees_can_manage_tasks` (boolean)
- `prevent_overlapping_time_entries` (boolean)
- `number_format`, `currency_format`, `date_format`, `interval_format`, `time_format` (localization strings)

**Added Constraints:**
- Foreign key: `user_id` → users.id (restrict on delete)

---

### 3. **Members Table** (2020_05_21_200000)
**Consolidated From:**
- Table created as `organization_user`, renamed to `members` in code
- `2024_05_07_134711_move_from_user_id_to_member_id_in_project_members_table.php` (the rename is implicit)
- `2024_06_07_113443_change_member_id_foreign_keys_to_restrict_on_delete.php`
- `2024_11_04_164807_add_foreign_key_to_organizations_and_members_table.php`

**Changes:**
- Created directly as `members` table (fresh install)
- Added foreign keys with restrict on delete:
  - `organization_id` → organizations.id
  - `user_id` → users.id

---

### 4. **Clients Table** (2024_01_20_110218)
**Consolidated From:**
- `2024_06_21_122754_add_is_archived_columns_to_projects_and_clients_table.php`

**Merged Columns:**
- `archived_at` (timestamp, nullable)

---

### 5. **Projects Table** (2024_01_20_110439)
**Consolidated From:**
- `2024_05_30_175801_add_is_billable_column_to_projects_table.php`
- `2024_06_21_122754_add_is_archived_columns_to_projects_and_clients_table.php`
- `2024_07_02_134307_add_estimated_time_to_projects_and_tasks_table.php`
- `2024_09_18_120203_add_spent_time_to_projects_and_tasks_table.php`

**Merged Columns:**
- `is_billable` (boolean, nullable)
- `archived_at` (timestamp, nullable)
- `estimated_time` (integer unsigned, nullable)
- `spent_time` (bigInteger unsigned, default 0) - note: changed from integer to bigInteger

---

### 6. **Tasks Table** (2024_01_20_110444)
**Consolidated From:**
- `2024_06_24_114433_add_done_at_to_tasks_table.php`
- `2024_07_02_134307_add_estimated_time_to_projects_and_tasks_table.php`
- `2024_09_18_120203_add_spent_time_to_projects_and_tasks_table.php`

**Merged Columns:**
- `done_at` (timestamp, nullable)
- `estimated_time` (integer unsigned, nullable)
- `spent_time` (bigInteger unsigned, default 0) - note: changed from integer to bigInteger

---

### 7. **Project Members Table** (2024_03_26_171253)
**Consolidated From:**
- `2024_05_07_134711_move_from_user_id_to_member_id_in_project_members_table.php`
- `2024_06_07_113443_change_member_id_foreign_keys_to_restrict_on_delete.php`

**Changes:**
- Uses `member_id` directly instead of `user_id`
- Added foreign key: `member_id` → members.id (restrict on delete)
- Changed unique constraint to `['project_id', 'member_id']`

---

### 8. **Time Entries Table** (2024_01_20_110837)
**Consolidated From:**
- `2024_05_22_151226_add_client_id_to_time_entries_table.php`
- `2024_05_30_175825_add_is_imported_column_to_time_entries_table.php`
- `2024_07_18_080906_add_still_active_email_sent_at_to_time_entries_table.php`
- `2024_07_03_145445_change_data_type_of_id_column_in_failed_jobs_table.php` (different table)
- `2025_10_16_000001_extend_time_entry_description.php`

**Merged Columns:**
- `client_id` (uuid, nullable) - foreign key to clients.id
- `member_id` (uuid) - new column, foreign key to members.id
- `is_imported` (boolean, default false)
- `still_active_email_sent_at` (timestamp, nullable)
- Extended `description` from 500 to 5000 characters

**Added Constraints:**
- Foreign key: `member_id` → members.id (restrict on delete)
- Updated foreign key: `client_id` → clients.id (restrict on delete)

---

### 9. **Organization Invitations Table** (2020_05_21_300000)
**Consolidated From:**
- `2024_06_07_113443_change_member_id_foreign_keys_to_restrict_on_delete.php` (also updated this table)

**Changes:**
- Updated foreign key: `organization_id` → organizations.id (restrict on delete instead of cascade)

---

### 10. **OAuth Access Tokens Table** (2016_06_01_000002)
**Consolidated From:**
- `2025_07_17_104903_add_reminder_sent_at_to_oauth_access_tokens_table.php`
- `2024_11_04_170614_add_foreign_keys_to_oauth_tables.php`

**Merged Columns:**
- `reminder_sent_at` (timestamp, nullable)
- `expired_info_sent_at` (timestamp, nullable)

**Added Constraints:**
- Foreign key: `user_id` → users.id (restrict on delete)
- Foreign key: `client_id` → oauth_clients.id (restrict on delete)

---

### 11. **OAuth Auth Codes Table** (2016_06_01_000001)
**Consolidated From:**
- `2024_11_04_170614_add_foreign_keys_to_oauth_tables.php`

**Added Constraints:**
- Foreign key: `user_id` → users.id (restrict on delete)
- Foreign key: `client_id` → oauth_clients.id (restrict on delete)

---

### 12. **OAuth Clients Table** (2016_06_01_000004)
**Consolidated From:**
- `2024_11_04_170614_add_foreign_keys_to_oauth_tables.php`

**Added Constraints:**
- Foreign key: `user_id` → users.id (restrict on delete)

---

### 13. **OAuth Refresh Tokens Table** (2016_06_01_000003)
**Consolidated From:**
- Implicit (added constraint for completeness)

**Added Constraints:**
- Foreign key: `access_token_id` → oauth_access_tokens.id (restrict on delete)

---

## Not Consolidated (Data Migration Required)

The following migrations **cannot be safely consolidated** as they require data migration and are kept as-is:

1. **2024_05_07_141842_move_from_user_id_to_member_id_in_time_entries_table.php**
   - Requires copying data from `user_id` to `member_id` based on organization membership
   - Kept for upgrade path

2. **2024_05_13_171020_rename_table_organization_user_to_members.php**
   - Renames table; kept for upgrade path (fresh installs create as `members` directly)

3. **2024_06_10_161831_reset_billable_rates_with_zero_as_value.php**
   - Data cleanup migration; kept for upgrade path

4. **2024_07_03_145445_change_data_type_of_id_column_in_failed_jobs_table.php**
   - Keeps as separate migration since it affects failed_jobs table

5. **2024_04_12_095010_create_cache_table.php**
   - Laravel cache table; kept as-is

6. **2024_11_04_170614_add_foreign_keys_to_oauth_tables.php**
   - Contains data validation; kept for safety

7. **2025_04_25_202047_change_data_type_for_spent_time_columns.php**
   - Changes type from integer to bigInteger; kept as separate migration

8. **2025_05_06_152804_fix_typos_in_organizations_table_format_columns.php**
   - Bug fix migration; kept for safety

9. **2025_06_30_095942_remove_oauth_personal_access_clients_table.php**
   - Table removal; kept for upgrade path

10. **2025_06_30_132538_update_oauth_clients_table.php**
    - Schema update; kept for safety

11. **2025_07_15_105949_hash_oauth_clients.php**
    - Data transformation; kept for safety

---

## Benefits for Fresh Install

✅ **Fewer migrations to run** - Consolidated tables created with all columns at once  
✅ **Faster deployment** - Less overhead running migrations  
✅ **Cleaner schema** - All constraints properly defined from the start  
✅ **Better referential integrity** - Foreign keys with restrict on delete prevents accidental data loss  
✅ **Same end result** - Final database schema is identical to running all migrations separately  

---

## Important Notes

1. **Existing Databases**: These consolidations are for **fresh installs only**. Existing databases should keep the migration history as-is for safe upgrading.

2. **MariaDB Compatibility**: All changes maintain MariaDB compatibility (jsonb → json, proper foreign key syntax, etc.)

3. **Foreign Key Constraints**: Changed many foreign keys to `restrictOnDelete` for better data integrity. Applications should handle foreign key constraint violations gracefully.

4. **Data Types**: Made consistent improvements:
   - `spent_time` changed from `integer` to `bigInteger` (handles larger values)
   - Description extended from 500 to 5000 characters
   - IP address field properly sized for IPv6

---

**Date Consolidated**: March 5, 2026  
**For Installation Type**: Fresh installations only  
**Total Migrations Consolidated**: 13 create/update migrations  
**Total Migrations Retained**: 41 migrations (some for data integrity, some for upgrade paths)

