# 🚀 QUICK START - Execute Migrate Fresh

## ⚡ Option 1: Run Fixed PowerShell Script

```powershell
cd d:\WEBSITE\DOCKER\AI-AUTH-02\laravel-auth-ai
.\cleanup-migrations.ps1
```

---

## ⚡ Option 2: Manual Step-by-Step (RECOMMENDED)

### Step 1: Delete Old Migration Files
```powershell
# Navigate to migrations folder
cd d:\WEBSITE\DOCKER\AI-AUTH-02\laravel-auth-ai\database\migrations

# Delete 4 old migration files (copy-paste each line)
Remove-Item "2026_04_17_create_roles_table.php" -Force
Remove-Item "2026_04_17_create_permissions_table.php" -Force
Remove-Item "2026_04_17_create_role_permission_table.php" -Force
Remove-Item "2026_04_17_create_user_role_table.php" -Force

# Verify they are deleted
Get-ChildItem -Filter "2026_04_17_create_*.php"
# Should return nothing
```

### Step 2: Verify New Migration Files Exist
```powershell
# Go back to project root
cd d:\WEBSITE\DOCKER\AI-AUTH-02\laravel-auth-ai

# Check new migrations are there
Get-ChildItem database\migrations -Filter "2026_04_17_23*.php"

# Should show:
# 2026_04_17_230000_create_roles_table.php
# 2026_04_17_230100_create_permissions_table.php
# 2026_04_17_230200_create_role_permission_table.php
# 2026_04_17_230300_create_user_role_table.php
```

### Step 3: Run Migrate Fresh
```powershell
php artisan migrate:fresh --seed
```

**Expected output:**
```
Rolling back: 2024_01_01_000000_create_users_table
Rolling back: ... (semua migrations di-rollback)
Migrating: 2024_01_01_000000_create_users_table
Migrating: ... (semua migrations di-migrate kembali)
Seeding: Database\Seeders\DatabaseSeeder
Seeding: Database\Seeders\RolePermissionSeeder
Seeding: Database\Seeders\UserRoleSeeder
```

### Step 4: Verify Data Created
```powershell
php artisan tinker
```

```php
>>> App\Models\Role::count()
=> 4

>>> App\Models\Permission::count()
=> 24

>>> App\Models\User::count()
=> 5

>>> App\Models\User::first()->roles
# Should show user with roles

>>> quit()
```

---

## ✅ Verification Checklist

- [ ] 4 old migration files deleted
- [ ] 4 new migration files exist in database/migrations
- [ ] Database migrated fresh without errors
- [ ] Seeders ran successfully
- [ ] 4 Roles created
- [ ] 24 Permissions created
- [ ] 5 Users created with roles assigned

---

## 🧪 Test Route Protection

### Test 1: Access as Super Admin
```bash
# Login dengan super-admin
Email: lazamediamxt@gmail.com
Password: password

# Coba akses protected routes
curl http://localhost:8000/dev/monitoring
# Should work (200 OK)

curl http://localhost:8000/dashboard/users
# Should work (200 OK)
```

### Test 2: Access as Regular User
```bash
# Login dengan regular user
Email: ahmad.fauzi@gmail.com
Password: password

# Coba akses protected routes
curl http://localhost:8000/dev/monitoring
# Should get 403 Forbidden (not super-admin)

curl http://localhost:8000/dashboard/users
# Should get 403 Forbidden (no permission:users.view)

curl http://localhost:8000/dashboard/profile
# Should work (200 OK - all users can access)
```

---

## 🔧 If Something Goes Wrong

### Error: "Base table or view already exists"
```powershell
php artisan migrate:reset
php artisan migrate:fresh --seed
```

### Error: "SQLSTATE[HY000]: General error: 1030"
```powershell
# Try fresh database
php artisan db:wipe
php artisan migrate
php artisan db:seed
```

### Error: "Method roles does not exist"
```powershell
# Model not loaded yet - just run again
php artisan migrate:fresh --seed
```

### Permissions not working on routes
```powershell
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan migrate:fresh --seed
```

---

## 🎯 Expected Result

After successful migration:

```
✅ 4 Roles created:
   - super-admin
   - admin
   - security-officer
   - user

✅ 24 Permissions created grouped by:
   - users (4)
   - login-logs (3)
   - devices (2)
   - otp (1)
   - ip-list (3)
   - settings (2)
   - dashboard (3)
   - rbac (5)

✅ 5 Users seeded:
   - 2 super-admin users
   - 3 regular users

✅ Routes protected with:
   - role:super-admin
   - permission:users.view
   - permission:users.edit
   - etc.
```

---

## 📊 Database Tables Created

```sql
CREATE TABLE roles (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255) UNIQUE,
  slug VARCHAR(255) UNIQUE,
  description VARCHAR(255),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE permissions (
  id BIGINT PRIMARY KEY,
  name VARCHAR(255) UNIQUE,
  slug VARCHAR(255) UNIQUE,
  description VARCHAR(255),
  group VARCHAR(255) DEFAULT 'general',
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE role_permission (
  id BIGINT PRIMARY KEY,
  role_id BIGINT,
  permission_id BIGINT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE(role_id, permission_id)
);

CREATE TABLE user_role (
  id BIGINT PRIMARY KEY,
  user_id BIGINT,
  role_id BIGINT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  UNIQUE(user_id, role_id)
);
```

---

## 🎉 You're Done!

Sistem Role & Permission Anda sekarang fully functional dan siap untuk production!

Next: 
- Read [ROLES_PERMISSIONS_GUIDE.md](ROLES_PERMISSIONS_GUIDE.md) untuk cara penggunaan
- Customize seeders sesuai kebutuhan Anda
- Add more roles/permissions sesuai business logic
