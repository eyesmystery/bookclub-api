<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLike;
use App\Models\BookReview;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create divisions for users (divisions still exist for users)
        Division::factory()->create(['name' => 'برج القراءة']);
        
        // Create test users
        $this->user = User::factory()->create([
            'division_id' => 1,
            'role' => 'user'
        ]);
        
        $this->admin = User::factory()->create([
            'division_id' => 1,
            'role' => 'admin'
        ]);
    }

    public function test_can_get_all_books_in_club_library()
    {
        // Create some books (without division_id)
        Book::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'books' => [
                    'data' => [
                        '*' => [
                            'id',
                            'title',
                            'author',
                            'description',
                            'cover_image',
                            'pdf_file',
                            'recommended_by_user_id',
                            'likes_count',
                            'reviews_count',
                            'user_has_liked',
                            'user_has_reviewed'
                        ]
                    ]
                ]
            ]);
    }

    public function test_can_search_books()
    {
        Book::factory()->create(['title' => 'Laravel Testing', 'author' => 'John Doe']);
        Book::factory()->create(['title' => 'PHP Basics', 'author' => 'Jane Smith']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/books?search=Laravel');

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('books.total'));
    }

    public function test_can_get_popular_books()
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        
        // Give book1 more likes
        BookLike::factory()->count(5)->create(['book_id' => $book1->id]);
        BookLike::factory()->count(2)->create(['book_id' => $book2->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/books/popular');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'books' => [
                    '*' => ['id', 'title', 'likes_count']
                ]
            ]);
    }

    public function test_can_get_recent_books()
    {
        Book::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/books/recent');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'books' => [
                    '*' => ['id', 'title', 'created_at']
                ]
            ]);
    }

    public function test_admin_can_create_book()
    {
        $bookData = [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'description' => 'Test Description',
            'cover_image' => 'https://example.com/cover.jpg',
            'pdf_file' => 'https://example.com/book.pdf',
            'recommended_by_user_id' => $this->user->id,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/books', $bookData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Book added to club library successfully'
            ]);

        $this->assertDatabaseHas('books', [
            'title' => 'Test Book',
            'author' => 'Test Author'
        ]);
    }

    public function test_user_cannot_create_book()
    {
        $bookData = [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'description' => 'Test Description',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/books', $bookData);

        $response->assertStatus(403);
    }

    public function test_can_show_specific_book()
    {
        $book = Book::factory()->create([
            'recommended_by_user_id' => $this->user->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'book' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'user_has_liked' => false,
                    'user_has_reviewed' => false
                ]
            ]);
    }

    public function test_admin_can_update_book()
    {
        $book = Book::factory()->create();

        $updateData = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/books/{$book->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Book updated successfully'
            ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
            'author' => 'Updated Author'
        ]);
    }

    public function test_admin_can_delete_book()
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Book removed from club library successfully'
            ]);

        $this->assertSoftDeleted('books', ['id' => $book->id]);
    }

    public function test_user_can_like_book()
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->user)
            ->postJson("/api/books/{$book->id}/like");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'liked' => true,
                'message' => 'Book liked successfully'
            ]);

        $this->assertDatabaseHas('book_likes', [
            'user_id' => $this->user->id,
            'book_id' => $book->id
        ]);
    }

    public function test_user_can_review_book()
    {
        $book = Book::factory()->create();

        $reviewData = [
            'rating' => 5,
            'comment' => 'Great book! Really enjoyed reading it.'
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/books/{$book->id}/review", $reviewData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Review added successfully'
            ]);

        $this->assertDatabaseHas('book_reviews', [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'rating' => 5
        ]);
    }

    public function test_user_cannot_review_same_book_twice()
    {
        $book = Book::factory()->create();
        
        // Create first review
        BookReview::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $book->id
        ]);

        $reviewData = [
            'rating' => 4,
            'comment' => 'Another review attempt'
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/books/{$book->id}/review", $reviewData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'You have already reviewed this book'
            ]);
    }

    public function test_guest_cannot_access_books()
    {
        $response = $this->getJson('/api/books');
        $response->assertStatus(401);
    }
} 