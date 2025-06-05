# BookClub API

A comprehensive REST API for managing a book club application built with Laravel 12, featuring user authentication, role-based access control, and management of divisions, books, events, articles, and news.

## ✅ Project Status

**The BookClub API is fully functional and ready for use!** 

✅ **All core features implemented:**
- User registration and authentication (Laravel Sanctum)
- Role-based access control (Admin/Member)
- CRUD operations for all entities
- Arabic text support
- Comprehensive validation
- Token-based API authentication
- Paginated responses

✅ **All tests passing:** 8/8 tests successful  
✅ **All endpoints working:** 33 API routes registered and functional  
✅ **Database populated:** Divisions seeded with Arabic names  

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+
- Composer
- MySQL/PostgreSQL/SQLite
- Laravel 12

### Installation

1. **Navigate to the project directory:**
```bash
cd bookclub-api
```

2. **Install dependencies:**
```bash
composer install
```

3. **Set up environment:**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database in `.env`:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookclub
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run migrations and seed:**
```bash
php artisan migrate:fresh --seed
```

6. **Start the development server:**
```bash
php artisan serve
```

The API will be available at `http://127.0.0.1:8000/api`

## 🧪 Test the API

Run the comprehensive test script:
```bash
./test_api.sh
```

Or run unit tests:
```bash
php artisan test
```

## 📚 API Documentation

### Base URL
```
http://127.0.0.1:8000/api
```

### Authentication
The API uses Laravel Sanctum for token-based authentication. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

### Divisions (القوائم)

Predefined divisions:
- برج القراءة (Reading Tower)
- برج الخبرة (Experience Tower)  
- برج الفلسفة (Philosophy Tower)
- برج السينما (Cinema Tower)

#### Get All Divisions
```http
GET /divisions
```

**Response:**
```json
{
  "divisions": [
    {
      "id": 1,
      "name": "برج القراءة",
      "users_count": 5,
      "books_count": 12,
      "events_count": 3,
      "articles_count": 8,
      "news_count": 2
    }
  ]
}
```

#### Get Division Details
```http
GET /divisions/{id}
```

### Authentication Endpoints

#### Register
```http
POST /register
Content-Type: application/json

{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "division_id": 1,
  "role": "member"
}
```

**Response:**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "division_id": 1,
    "role": "member",
    "division": {
      "id": 1,
      "name": "برج القراءة"
    }
  },
  "token": "1|abc123..."
}
```

#### Login
```http
POST /login
Content-Type: application/json

{
  "email": "ahmed@example.com",
  "password": "password123"
}
```

#### Get User Profile
```http
GET /user
Authorization: Bearer {token}
```

#### Logout
```http
POST /logout
Authorization: Bearer {token}
```

### Books (الكتب)

All book endpoints require authentication. Create/Update/Delete require admin role.

#### Get Books
```http
GET /books
Authorization: Bearer {token}

# Optional query parameters:
# ?division_id=1 - Filter by division
```

#### Create Book (Admin only)
```http
POST /books
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "عنوان الكتاب",
  "author": "اسم المؤلف",
  "description": "وصف الكتاب",
  "cover_image": "https://example.com/cover.jpg",
  "division_id": 1,
  "recommended_by_user_id": 1
}
```

#### Get Book Details
```http
GET /books/{id}
Authorization: Bearer {token}
```

#### Update Book (Admin only)
```http
PUT /books/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "العنوان المحدث"
}
```

#### Delete Book (Admin only)
```http
DELETE /books/{id}
Authorization: Bearer {token}
```

### Events (الفعاليات)

All event endpoints require authentication. Create/Update/Delete require admin role.

#### Get Events
```http
GET /events
Authorization: Bearer {token}

# Optional query parameters:
# ?division_id=1 - Filter by division
# ?upcoming=1 - Only upcoming events
```

#### Create Event (Admin only)
```http
POST /events
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "عنوان الفعالية",
  "description": "وصف الفعالية",
  "date": "2024-12-25 18:00:00",
  "location": "موقع الفعالية",
  "division_id": 1
}
```

### Articles (المقالات)

All article endpoints require authentication. Create/Update/Delete require admin role.

#### Get Articles
```http
GET /articles
Authorization: Bearer {token}

# Optional query parameters:
# ?division_id=1 - Filter by division
# ?published=1 - Only published articles
```

#### Create Article (Admin only)
```http
POST /articles
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "عنوان المقال",
  "body": "محتوى المقال",
  "division_id": 1,
  "published_at": true
}
```

### News (الأخبار)

All news endpoints require authentication. Create/Update/Delete require admin role.

#### Get News
```http
GET /news
Authorization: Bearer {token}

# Optional query parameters:
# ?division_id=1 - Filter by division
# ?published=1 - Only published news
```

#### Create News (Admin only)
```http
POST /news
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "عنوان الخبر",
  "body": "محتوى الخبر",
  "division_id": 1,
  "published_at": true
}
```

### Users (المستخدمون)

#### Get Users (Admin only)
```http
GET /users
Authorization: Bearer {token}

# Optional query parameters:
# ?division_id=1 - Filter by division
# ?role=admin - Filter by role
```

#### Get User Profile
```http
GET /users/{id}
Authorization: Bearer {token}
```
*Note: Users can only see their own profile unless they're admin*

#### Update User
```http
PUT /users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "الاسم الجديد",
  "email": "email@example.com",
  "division_id": 2
}
```
*Note: Only admins can change user roles*

#### Delete User (Admin only)
```http
DELETE /users/{id}
Authorization: Bearer {token}
```

## 🔐 Role-Based Access Control

### Member Role (member)
- Can view all divisions, books, events, articles, and news
- Can manage their own profile
- Cannot create, update, or delete content

### Admin Role (admin)
- Full access to all endpoints
- Can create, update, and delete all content
- Can manage all users
- Can change user roles

## 📊 Database Schema

### Users
- `id`, `name`, `email`, `password`
- `division_id` (foreign key to divisions)
- `role` (enum: admin, member)
- `email_verified_at`, `created_at`, `updated_at`

### Divisions
- `id`, `name`
- `created_at`, `updated_at`

### Books
- `id`, `title`, `author`, `description`, `cover_image`
- `division_id` (foreign key to divisions)
- `recommended_by_user_id` (foreign key to users)
- `created_at`, `updated_at`

### Events
- `id`, `title`, `description`, `date`, `location`
- `division_id` (foreign key to divisions)
- `created_at`, `updated_at`

### Articles
- `id`, `title`, `body`, `published_at`
- `author_id` (foreign key to users)
- `division_id` (foreign key to divisions)
- `created_at`, `updated_at`

### News
- `id`, `title`, `body`, `published_at`
- `division_id` (foreign key to divisions)
- `created_at`, `updated_at`

## 🛡️ Security Features

- **Token-based authentication** using Laravel Sanctum
- **Password hashing** using bcrypt
- **Input validation** for all endpoints
- **Role-based authorization** middleware
- **CORS support** for web applications
- **Rate limiting** (Laravel default)

## 🚨 Error Handling

The API returns consistent JSON error responses:

### Validation Errors (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password confirmation does not match."]
  }
}
```

### Authentication Errors (401)
```json
{
  "message": "Unauthenticated."
}
```

### Authorization Errors (403)
```json
{
  "message": "Access denied."
}
```

### Not Found Errors (404)
```json
{
  "message": "Resource not found."
}
```

## 🧪 Testing

The project includes comprehensive tests covering:

- User registration and authentication
- Login and logout functionality
- Token-based API access
- Validation rules
- Error handling

Run tests:
```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test --filter AuthTest

# Run with coverage
php artisan test --coverage
```

## 📦 Dependencies

- **Laravel Framework 12.x** - PHP web framework
- **Laravel Sanctum** - API token authentication
- **Laravel Tinker** - REPL for Laravel
- **PHPUnit** - Testing framework

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 📞 Support

For support or questions, please open an issue in the GitHub repository.

---

**Built with ❤️ using Laravel 12 and Arabic language support**
