# Laravel 12 Upgrade Plan

## Overview
This Laravel application has been partially upgraded from Laravel 7 to Laravel 12. This document outlines the remaining steps to complete the upgrade.

## Pre-Upgrade Checklist
- [ ] Backup database
- [ ] Create feature branch for upgrade
- [ ] Ensure all tests pass on current version
- [ ] Document current Laravel version components

## Step-by-Step Upgrade Plan

### Phase 1: Critical Infrastructure Updates (High Priority)

#### Step 1: Update Middleware
**Files to update:**
- `app/Http/Middleware/CheckForMaintenanceMode.php`
- `app/Http/Kernel.php`

**Actions:**
1. Rename `CheckForMaintenanceMode` to `PreventRequestsDuringMaintenance`
2. Update middleware class reference in `app/Http/Kernel.php`
3. Change `$routeMiddleware` property to `$middlewareAliases` in Kernel

**Commands:**
```bash
# Test middleware functionality
php artisan route:list
```

#### Step 2: Update Route Definitions
**File:** `routes/api.php`

**Actions:**
1. Convert string-based controller references to class-based
2. Add use statements for all controllers at top of file

**Example changes:**
```php
// Before
Route::post('login', 'Auth\AuthController@login');

// After
use App\Http\Controllers\Auth\AuthController;
Route::post('login', [AuthController::class, 'login']);
```

**Commands:**
```bash
php artisan route:list
php artisan route:cache
```

#### Step 3: Update Route Service Provider
**File:** `app/Providers/RouteServiceProvider.php`

**Actions:**
1. Remove deprecated `$namespace` property
2. Update route registration methods
3. Remove manual route model binding if present

### Phase 2: Model Relationship Updates (Critical)

#### Step 4: Update Model Relationships
**Files to update:**
- `app/Models/User.php`
- `app/Models/Post.php` 
- `app/Models/Comment.php`
- `app/Models/Forum.php`
- `app/Models/Event.php`
- `app/Models/Calendar.php`
- All other model files

**Actions:**
1. Replace string-based relationship definitions with class references
2. Add necessary use statements

**Example changes:**
```php
// Before
return $this->hasMany('App\Models\Post');
return $this->belongsTo('App\Models\User');

// After  
return $this->hasMany(Post::class);
return $this->belongsTo(User::class);
```

**Commands:**
```bash
php artisan tinker
# Test model relationships work correctly
User::first()->posts;
```

### Phase 3: Configuration Updates (Medium Priority)

#### Step 5: Update Authentication Configuration
**File:** `config/auth.php`

**Actions:**
1. Ensure API guard uses 'sanctum' driver instead of deprecated 'token'
2. Verify all guard configurations are Laravel 12 compatible

#### Step 6: Clean Up Service Providers
**File:** `app/Providers/AuthServiceProvider.php`

**Actions:**
1. Remove unused Passport import if not using Passport
2. Verify policy mappings are correct for Laravel 12

#### Step 7: Update TrustProxies Middleware
**File:** `app/Http/Middleware/TrustProxies.php`

**Actions:**
1. Update deprecated Request header constants
2. Use `Request::HEADER_X_FORWARDED_ALL` or newer patterns

### Phase 4: Database and Migration Updates (Medium Priority)

#### Step 8: Review Database Migrations
**Actions:**
1. Check all migrations for deprecated methods
2. Update any deprecated column types or constraints
3. Verify foreign key constraint syntax

**Commands:**
```bash
php artisan migrate:status
php artisan migrate --pretend
```

#### Step 9: Update Factories and Seeders
**Files:** `database/factories/*` and `database/seeders/*`

**Actions:**
1. Update factory definitions to Laravel 12 syntax
2. Verify seeder class calls are correct

### Phase 5: Frontend and Asset Updates (Low Priority)

#### Step 10: Update Mix Configuration
**File:** `webpack.mix.js`

**Actions:**
1. Update Laravel Mix to version compatible with Laravel 12
2. Verify asset compilation still works

**Commands:**
```bash
npm install
npm run dev
```

#### Step 11: Update Blade Templates
**Files:** `resources/views/*`

**Actions:**
1. Check for deprecated Blade directives
2. Update any deprecated template patterns

### Phase 6: Testing and Validation

#### Step 12: Update Test Suite
**Actions:**
1. Run existing tests to identify failures
2. Update test cases for Laravel 12 compatibility
3. Add tests for upgraded functionality

**Commands:**
```bash
php artisan test
php artisan test --coverage
```

#### Step 13: Update Dependencies
**Actions:**
1. Review `composer.json` for deprecated packages
2. Update third-party packages to Laravel 12 compatible versions
3. Remove any packages no longer needed

**Commands:**
```bash
composer outdated
composer update
```

### Phase 7: Performance and Security

#### Step 14: Update Security Configurations
**Actions:**
1. Review security configurations for Laravel 12 best practices
2. Update CORS settings if needed
3. Verify CSRF protection is properly configured

#### Step 15: Optimize for Laravel 12
**Actions:**
1. Clear and rebuild all caches
2. Update config caching
3. Verify queue configuration

**Commands:**
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Post-Upgrade Tasks

### Verification Checklist
- [ ] All routes accessible and functional
- [ ] Authentication and authorization working
- [ ] Database operations successful
- [ ] File uploads working
- [ ] Email notifications functional
- [ ] Queue jobs processing
- [ ] Frontend assets loading
- [ ] All tests passing

### Performance Testing
- [ ] Load testing API endpoints
- [ ] Verify cache performance
- [ ] Check memory usage patterns
- [ ] Validate database query performance

## Rollback Plan
1. Revert to backup database
2. Checkout previous git commit
3. Restore previous `composer.json`
4. Run `composer install`
5. Clear all caches

## Estimated Timeline
- **Phase 1**: 1-2 days
- **Phase 2**: 2-3 days  
- **Phase 3**: 1 day
- **Phase 4**: 1-2 days
- **Phase 5**: 1 day
- **Phase 6**: 2-3 days
- **Phase 7**: 1 day

**Total Estimated Time**: 9-13 days

## Risk Assessment
- **High Risk**: Model relationship changes (Phase 2)
- **Medium Risk**: Route definition updates (Phase 1)
- **Low Risk**: Configuration updates (Phase 3)

## Notes
- Test each phase thoroughly before proceeding
- Keep detailed logs of changes made
- Have rollback plan ready at each phase
- Consider upgrading in smaller increments (Laravel 8 → 9 → 10 → 11 → 12) if issues arise