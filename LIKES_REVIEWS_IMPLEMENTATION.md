# Book Likes & Reviews Implementation

This document describes the implementation of likes and reviews functionality for the BookClub API.

## 🟠 Likes Feature

### Database Structure
- **Table**: `book_likes`
- **Columns**: `id`, `user_id`, `book_id`, `timestamps`
- **Constraints**: Unique constraint on `(user_id, book_id)` to prevent duplicate likes
- **Foreign Keys**: Cascade delete on user and book deletion

### Model: BookLike
- **Location**: `app/Models/BookLike.php`
- **Fillable**: `user_id`, `book_id`
- **Relationships**:
  - `user()` - belongsTo User
  - `book()` - belongsTo Book

### API Endpoint
- **POST** `/api/books/{id}/like`
- **Authentication**: Required (Laravel Sanctum)
- **Functionality**: Toggle like/unlike
- **Response**:
  ```json
  {
    "message": "Book liked successfully",
    "liked": true,
    "likes_count": 5
  }
  ```

### Book Model Updates
- Added `likes()` relationship (hasMany BookLike)
- Added `isLikedByUser($userId)` helper method
- Book responses now include `likes_count` via `withCount(['likes'])`
- Book detail endpoint includes `is_liked_by_user` for authenticated users

## 💬 Reviews Feature

### Database Structure
- **Table**: `book_reviews`
- **Columns**: `id`, `user_id`, `book_id`, `content`, `timestamps`
- **Indexes**: Composite index on `(book_id, created_at)` for performance
- **Foreign Keys**: Cascade delete on user and book deletion

### Model: BookReview
- **Location**: `app/Models/BookReview.php`
- **Fillable**: `user_id`, `book_id`, `content`
- **Relationships**:
  - `user()` - belongsTo User
  - `book()` - belongsTo Book

### Form Request Validation
- **Class**: `StoreBookReviewRequest`
- **Rules**:
  - `content`: required, string, min:10, max:2000
- **Authorization**: Authenticated users only

### API Endpoints

#### 1. Add Review
- **POST** `/api/books/{id}/review`
- **Authentication**: Required
- **Validation**: Content required (10-2000 characters)
- **Response**:
  ```json
  {
    "message": "Review added successfully",
    "review": {
      "id": 1,
      "content": "Great book!",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  }
  ```

#### 2. Get Book Reviews
- **GET** `/api/books/{id}/reviews`
- **Authentication**: Required
- **Pagination**: 15 reviews per page
- **Response**: Paginated reviews with user information

#### 3. Get Reviewed Books
- **GET** `/api/books/reviewed`
- **Authentication**: Required
- **Functionality**: Returns books that have at least one review
- **Response**: Paginated books with likes and reviews counts

### Book Model Updates
- Added `reviews()` relationship (hasMany BookReview)
- Book responses now include `reviews_count` via `withCount(['reviews'])`

## 🔧 User Model Updates

Added relationships:
- `bookLikes()` - hasMany BookLike
- `bookReviews()` - hasMany BookReview

## 📝 Controllers

### BookLikeController
- **Method**: `toggle(Request $request, Book $book)`
- **Logic**: Checks for existing like, toggles state, returns updated count

### BookReviewController
- **Methods**:
  - `store(StoreBookReviewRequest $request, Book $book)` - Add review
  - `index(Request $request, Book $book)` - Get book reviews
  - `reviewedBooks(Request $request)` - Get books with reviews

### BookController Updates
- All book endpoints now include `likes_count` and `reviews_count`
- Book detail endpoint includes `is_liked_by_user` status

## 🛣️ Routes

All routes require authentication (`auth:sanctum` middleware):

```php
// Book likes
Route::post('books/{book}/like', [BookLikeController::class, 'toggle']);

// Book reviews
Route::get('books/reviewed', [BookReviewController::class, 'reviewedBooks']);
Route::post('books/{book}/review', [BookReviewController::class, 'store']);
Route::get('books/{book}/reviews', [BookReviewController::class, 'index']);
```

## 🧪 Testing

Use the provided test script:
```bash
chmod +x test_likes_reviews.sh
./test_likes_reviews.sh
```

The script tests:
- Like/unlike functionality
- Review creation and retrieval
- Count updates
- User like status
- Reviewed books endpoint

## 🚀 Deployment

1. Run migrations:
   ```bash
   php artisan migrate
   ```

2. Ensure Laravel Sanctum is properly configured for API authentication

3. Test all endpoints with the provided test script

## 📋 Features Summary

✅ **Implemented Features:**
- Toggle book likes (prevent duplicates)
- Add book reviews with validation
- Get reviews for specific books
- Get books that have reviews
- Like and review counts on all book responses
- User like status on book details
- Proper authentication and authorization
- Cascade deletion for data integrity
- Performance indexes for queries

✅ **Laravel Best Practices:**
- Form Request validation
- Eloquent relationships
- Resource controllers
- Middleware authentication
- Proper error handling
- Clean API responses
- Database constraints and indexes 