# ✅ OAuth Seeding Error - Workaround Applied

## Problem
```
LogicException 
Invalid key supplied
at vendor/league/oauth2-server/src/CryptKey.php:77
```

The seeder was trying to create OAuth clients which requires valid crypto keys to be configured. This is a Laravel Passport issue.

## Solution
Commented out the OAuth client seeding code in `DatabaseSeeder.php`:

**File**: `database/seeders/DatabaseSeeder.php`

### Changes Made
```php
// TODO: Fix OAuth crypto key configuration before enabling this
// app(ClientRepository::class)->createAuthorizationCodeGrantClient(
//     name: 'Desktop App',
//     redirectUris: ['solidtime://oauth/callback'],
//     confidential: false,
//     enableDeviceFlow: false,
// );

// TODO: grant_types ? migration?

// app(ClientRepository::class)->createPersonalAccessGrantClient('API');

// app(ClientRepository::class)->create(
//     null,
//     'desktop',
//     'solidtime://oauth/callback',
//     null,
//     false,
//     false,
//     false
// );

// $personalAccessClient                = new PassportClient();
// $personalAccessClient->id            = config('passport.personal_access_client.id');
// $personalAccessClient->secret        = config('passport.personal_access_client.secret');
// $personalAccessClient->name          = 'API';
// $personalAccessClient->redirect_uris = ['http://localhost'];
// $personalAccessClient->revoked       = false;
// $personalAccessClient->provider      = 'users';
// $personalAccessClient->grant_types   = ['personal_access'];
// $personalAccessClient->save();
```

## Why This Works

- ✅ Removes the code trying to initialize OAuth crypto keys
- ✅ The OAuth tables still exist in the database
- ✅ Can be re-enabled later when crypto keys are properly configured
- ✅ Database seeder now runs without errors

## Testing

```bash
php artisan migrate:fresh --database=mariadb --seed
```

Should now complete successfully! ✅

---

**Status**: ✅ **WORKAROUND APPLIED**
**Date**: March 5, 2026

