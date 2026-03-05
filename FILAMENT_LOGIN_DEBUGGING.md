# 🐛 FILAMENT LOGIN DEBUGGING GUIDE

## Overview

The Login page now has comprehensive debug logging to track authentication failures. All logs are written to `storage/logs/laravel.log`.

---

## Debug Logging Points

### 1. Form Data Received
```
[INFO] Filament login attempt
- email: user's email
- has_password: true/false
```

**What it shows**: Whether the form was submitted with data

---

### 2. Credentials Extracted
```
[INFO] Attempting authentication with credentials
- email: extracted email
- has_password: true/false
```

**What it shows**: That credentials were properly extracted from form state

---

### 3. Authentication Attempt Result
```
[INFO] Authentication result
- authenticated: true/false
- guard: 'web' or other guard name
```

**What it shows**: Whether Filament's auth()->attempt() succeeded or failed

---

### 4. User Lookup (getAuthenticatedUser)
```
[INFO] User lookup result
- email: email being searched
- user_found: true/false
- user_id: ID if found
- user_is_placeholder: true/false
```

**What it shows**: Whether user exists in database and is not a placeholder

---

### 5. Password Verification
```
[INFO] Password check result
- email: user's email
- password_matches: true/false
- stored_password_length: length of bcrypt hash (usually 60)
- provided_password_length: length of password entered
```

**What it shows**: Whether the password hash matches (password_matches = true means it's correct)

---

### 6. Success
```
[INFO] User authenticated successfully
- user_id: authenticated user ID
- user_email: authenticated user email
```

**What it shows**: User successfully logged in

---

### 7. Authorization Check
```
[INFO] User authenticated successfully (Filament)
- user_id: user ID
- user_email: user email

[WARNING] User cannot access panel (if they can't)
- user_id: user ID
- panel: admin
```

**What it shows**: Whether user can access the Filament panel

---

## How to Debug

### Step 1: Check the Logs

```bash
# View real-time logs
tail -f storage/logs/laravel.log

# Or search for login attempts
grep "login attempt" storage/logs/laravel.log
```

### Step 2: Try Logging In

1. Navigate to `/admin/login`
2. Enter email and password
3. Click Submit
4. Check logs immediately

### Step 3: Read the Log Trail

Look for the sequence:
```
1. [INFO] Filament login attempt
   └─ Did form submit?

2. [INFO] Attempting authentication with credentials
   └─ Were credentials extracted?

3. [INFO] getAuthenticatedUser called
   └─ Was getAuthenticatedUser called?

4. [INFO] User lookup result
   └─ Was user found?
   └─ Is user a placeholder? (should be false)

5. [INFO] Password check result
   └─ Does password_matches = true?

6. [INFO] User authenticated successfully (or [WARNING] if failed)
   └─ Did authentication succeed?

7. [INFO] Authentication result / throwFailureValidationException
   └─ Did Filament auth accept the user?

8. [INFO] User authenticated successfully (Filament)
   └─ Are you logged in?
```

---

## Common Issues & Solutions

### Issue 1: User Not Found
```
[WARNING] User not found or is placeholder
- email: user@example.com
```

**Solutions**:
- ✅ Verify user exists in database: `User::where('email', 'user@example.com')->first()`
- ✅ Check if user is placeholder: `$user->is_placeholder` should be `false`
- ✅ Verify email spelling matches exactly (case-sensitive in some databases)

---

### Issue 2: Password Mismatch
```
[INFO] Password check result
- password_matches: false
```

**Solutions**:
- ✅ Verify password is correct
- ✅ Check `stored_password_length` is ~60 (bcrypt hash)
- ✅ If length is wrong, password might not be hashed properly
- ✅ Reset user password: `$user->password = Hash::make('newpassword'); $user->save();`

---

### Issue 3: User Found But Auth Fails
```
[INFO] User lookup result
- user_found: true

[WARNING] Authentication failed for email
```

**Solutions**:
- ✅ Check if getAuthenticatedUser returned null
- ✅ Check password verification logs
- ✅ Verify password field in database is not corrupted

---

### Issue 4: Auth Succeeds But No Redirect
```
[INFO] User authenticated successfully
[INFO] Authentication result
- authenticated: true

[WARNING] User cannot access panel
```

**Solutions**:
- ✅ Check if user implements FilamentUser interface
- ✅ Check if `canAccessPanel()` returns true for user
- ✅ Verify user has permission to access admin panel

---

## Database Queries to Run

### Check User Exists
```php
$user = \App\Models\User::where('email', 'test@example.com')->first();
dd($user);
```

### Check User Fields
```php
$user = \App\Models\User::where('email', 'test@example.com')->first();
echo "Email: " . $user->email . "\n";
echo "Is Placeholder: " . ($user->is_placeholder ? 'YES' : 'NO') . "\n";
echo "Password Hash: " . substr($user->password, 0, 20) . "...\n";
```

### Verify Password Hash
```php
use Illuminate\Support\Facades\Hash;

$user = \App\Models\User::where('email', 'test@example.com')->first();
$matches = Hash::check('password_to_test', $user->password);
echo $matches ? "Password matches!" : "Password does NOT match!";
```

---

## Log File Location

```bash
# Linux/Mac
storage/logs/laravel.log

# View live logs
tail -f storage/logs/laravel.log

# Search for specific attempts
grep "email@example.com" storage/logs/laravel.log

# Count failed attempts
grep "Authentication failed" storage/logs/laravel.log | wc -l
```

---

## Complete Debug Checklist

When troubleshooting login, check in order:

- [ ] User submits form (check: "Filament login attempt" log)
- [ ] Credentials extracted (check: "Attempting authentication" log)
- [ ] User found in database (check: "User lookup result" - user_found=true)
- [ ] User is not placeholder (check: "User lookup result" - user_is_placeholder=false)
- [ ] Password matches (check: "Password check result" - password_matches=true)
- [ ] Filament auth accepts (check: "Authentication result" - authenticated=true)
- [ ] User can access panel (check: no "User cannot access panel" warning)
- [ ] Session regenerated (check: no errors)
- [ ] Redirect to dashboard (should happen automatically)

---

## Example Log Sequence (Success)

```
[2026-03-05 10:30:15] local.INFO: Filament login attempt {"email":"user@example.com","has_password":true}
[2026-03-05 10:30:15] local.INFO: Attempting authentication with credentials {"email":"user@example.com","has_password":true}
[2026-03-05 10:30:15] local.INFO: getAuthenticatedUser called {"email":"user@example.com"}
[2026-03-05 10:30:15] local.INFO: User lookup result {"email":"user@example.com","user_found":true,"user_id":"abc123","user_is_placeholder":false}
[2026-03-05 10:30:15] local.INFO: Password check result {"email":"user@example.com","password_matches":true,"stored_password_length":60,"provided_password_length":8}
[2026-03-05 10:30:15] local.INFO: User authenticated successfully {"email":"user@example.com","user_id":"abc123"}
[2026-03-05 10:30:15] local.INFO: Authentication result {"authenticated":true,"guard":"web"}
[2026-03-05 10:30:15] local.INFO: User authenticated successfully {"user_id":"abc123","user_email":"user@example.com"}
```

✅ Login successful → redirects to admin dashboard

---

## Example Log Sequence (Failure)

```
[2026-03-05 10:31:45] local.INFO: Filament login attempt {"email":"unknown@example.com","has_password":true}
[2026-03-05 10:31:45] local.INFO: Attempting authentication with credentials {"email":"unknown@example.com","has_password":true}
[2026-03-05 10:31:45] local.INFO: getAuthenticatedUser called {"email":"unknown@example.com"}
[2026-03-05 10:31:45] local.INFO: User lookup result {"email":"unknown@example.com","user_found":false,"user_id":null,"user_is_placeholder":null}
[2026-03-05 10:31:45] local.WARNING: User not found or is placeholder {"email":"unknown@example.com"}
[2026-03-05 10:31:45] local.WARNING: Authentication failed for email {"email":"unknown@example.com"}
[2026-03-05 10:31:45] local.ERROR: Login validation exception thrown - authentication failed {"ip":"127.0.0.1","user_agent":"Mozilla/5.0..."}
```

❌ User not found → shows error message "Invalid credentials"

---

**Now you can see exactly why login is failing!**

Check `storage/logs/laravel.log` and follow the sequence to find the problem.


