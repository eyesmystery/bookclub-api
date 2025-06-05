# Admin Role-Based Authorization Implementation

This document describes the implementation of role-based authorization for the BookClub API.

## 🔐 Role System Overview

### Supported Roles
- **admin**: Full access to all admin functions
- **moderator**: (Future use - currently not implemented with specific permissions)
- **user**: Regular user with standard permissions

### Database Structure
- **Table**: `users`
- **Column**: `role` (enum: 'admin', 'moderator', 'user')
- **Default**: `user`

## 🛡️ EnsureAdmin Middleware

### Location
`app/Http/Middleware/EnsureAdmin.php`

### Functionality
- Checks if user is authenticated
- Verifies user role is 'admin'
- Returns 401 if not authenticated
- Returns 403 if not admin

### Implementation
```php
public function handle(Request $request, Closure $next): Response
{
    if (!$request->user()) {
        return response()->json([
            'message' => 'Authentication required.',
        ], 401);
    }

    if ($request->user()->role !== 'admin') {
        return response()->json([
            'message' => 'Access denied. Admin privileges required.',
        ], 403);
    }

    return $next($request);
}
```

### Registration
Middleware is registered in `bootstrap/app.php`:
```php
$middleware->alias([
    'admin' => \App\Http\Middleware\EnsureAdmin::class,
]);
```

## 👤 User Model Enhancements

### Helper Methods
```php
/**
 * Check if the user is an admin.
 */
public function isAdmin()
{
    return $this->role === 'admin';
}

/**
 * Check if the user is a moderator.
 */
public function isModerator()
{
    return $this->role === 'moderator';
}

/**
 * Check if the user is a regular user.
 */
public function isUser()
{
    return $this->role === 'user';
}

/**
 * Check if the user is a member (backward compatibility).
 */
public function isMember()
{
    return in_array($this->role, ['member', 'user']);
}
```

## 🔍 User Endpoint Updates

### GET `/api/user`
Now includes the user's role in the response:
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "admin",
    "division": {
      "id": 1,
      "name": "Fiction"
    }
  }
}
```

## 🛣️ Admin-Protected Routes

### Book Management (Admin Only)
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('books', [BookController::class, 'store']);
    Route::put('books/{book}', [BookController::class, 'update']);
    Route::delete('books/{book}', [BookController::class, 'destroy']);
});
```

### Article Management (Admin Only)
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('articles', [ArticleController::class, 'store']);
    Route::put('articles/{article}', [ArticleController::class, 'update']);
    Route::delete('articles/{article}', [ArticleController::class, 'destroy']);
});
```

### Review Management (Admin Only)
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::delete('reviews/{review}', [BookReviewController::class, 'destroy']);
});
```

### Division Management (Admin Only)
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('divisions', [DivisionController::class, 'store']);
    Route::put('divisions/{division}', [DivisionController::class, 'update']);
    Route::delete('divisions/{division}', [DivisionController::class, 'destroy']);
});
```

### User Management (Admin Only)
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('users', [UserController::class, 'index']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
});
```

### News & Events Management (Admin Only)
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('news', [NewsController::class, 'store']);
    Route::put('news/{news}', [NewsController::class, 'update']);
    Route::delete('news/{news}', [NewsController::class, 'destroy']);
    
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy']);
});
```

## 📝 Validation Updates

### Registration Request
Updated to support new role values:
```php
'role' => 'sometimes|in:admin,moderator,user'
```

### User Update Controller
Only admins can change user roles:
```php
// Only admins can change roles
if (auth()->user()->isAdmin()) {
    $rules['role'] = 'sometimes|in:admin,moderator,user';
}
```

### Authentication Controller
Default role set to 'user':
```php
'role' => $request->role ?? 'user'
```

## 🗃️ Database Migration

### Migration: `add_fields_to_users_table.php`
```php
// Updated to support three roles with proper handling of existing users
$table->enum('role', ['admin', 'moderator', 'user'])->default('user');
```

### Backward Compatibility
- Existing 'member' roles are converted to 'user'
- `isMember()` method still works for backward compatibility

## 🧪 Testing

### Test Script
Use the provided test script to verify functionality:
```bash
chmod +x test_admin_roles.sh
./test_admin_roles.sh
```

### Test Coverage
- User registration with different roles
- Role information in user endpoint
- Admin-protected endpoint access control
- Book, article, and review management
- Proper error responses (401, 403)

## 🔒 Error Responses

### 401 Unauthorized (Not Authenticated)
```json
{
  "message": "Authentication required."
}
```

### 403 Forbidden (Not Admin)
```json
{
  "message": "Access denied. Admin privileges required."
}
```

## 📋 Summary of Changes

### New Files
- `app/Http/Middleware/EnsureAdmin.php`
- `test_admin_roles.sh`
- `ADMIN_ROLES_IMPLEMENTATION.md`

### Modified Files
- `database/migrations/2025_06_04_160905_add_fields_to_users_table.php` - Updated role enum
- `app/Models/User.php` - Added role helper methods
- `app/Http/Controllers/Api/AuthController.php` - Updated default role
- `app/Http/Controllers/Api/UserController.php` - Updated role validation
- `app/Http/Controllers/Api/BookReviewController.php` - Added destroy method
- `app/Http/Requests/RegisterRequest.php` - Updated role validation
- `database/factories/UserFactory.php` - Updated default role
- `bootstrap/app.php` - Updated middleware alias
- `routes/api.php` - Added admin review management route

### Key Features Implemented
✅ **Role-based authorization**: Admin, moderator, user roles
✅ **EnsureAdmin middleware**: Proper authentication and authorization checks
✅ **User endpoint enhancement**: Role information included in responses
✅ **Admin-protected routes**: All specified endpoints properly protected
✅ **Validation updates**: Role validation in registration and user updates
✅ **Helper methods**: Clean role checking methods on User model
✅ **Error handling**: Appropriate HTTP status codes and messages
✅ **Testing**: Comprehensive test script for verification
✅ **Documentation**: Complete implementation documentation

### Laravel Best Practices Applied
- Middleware for authorization
- Form request validation
- Eloquent relationships and helpers
- Proper HTTP status codes
- Clean separation of concerns
- Database constraints and migrations 