<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create divisions for testing
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

    public function test_authenticated_user_can_get_articles_list(): void
    {
        Article::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/articles');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'articles' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'content',
                                'excerpt',
                                'author_id',
                                'division_id',
                                'featured_image',
                                'is_published',
                                'published_at',
                                'created_at',
                                'updated_at',
                                'author',
                                'division'
                            ]
                        ],
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }

    public function test_unauthenticated_user_cannot_access_articles(): void
    {
        $response = $this->getJson('/api/articles');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_authenticated_user_can_get_single_article(): void
    {
        $article = Article::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'article' => [
                        'id',
                        'title',
                        'content',
                        'excerpt',
                        'author_id',
                        'division_id',
                        'featured_image',
                        'is_published',
                        'published_at',
                        'created_at',
                        'updated_at',
                        'author',
                        'division'
                    ]
                ]);
    }

    public function test_admin_can_create_article(): void
    {
        $articleData = [
            'title' => 'Test Article',
            'content' => 'This is a test article content',
            'excerpt' => 'Test excerpt',
            'author_id' => $this->admin->id,
            'division_id' => 1,
            'is_published' => true
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/articles', $articleData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'article' => [
                        'id',
                        'title',
                        'content',
                        'excerpt',
                        'author_id',
                        'division_id',
                        'is_published'
                    ]
                ]);

        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article',
            'content' => 'This is a test article content'
        ]);
    }

    public function test_regular_user_cannot_create_article(): void
    {
        $articleData = [
            'title' => 'Test Article',
            'content' => 'This is a test article content'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/articles', $articleData);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_article(): void
    {
        $article = Article::factory()->create();
        
        $updateData = [
            'title' => 'Updated Article Title',
            'content' => 'Updated article content',
            'excerpt' => 'Updated excerpt'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson("/api/articles/{$article->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'article'
                ]);

        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'title' => 'Updated Article Title',
            'content' => 'Updated article content'
        ]);
    }

    public function test_admin_can_delete_article(): void
    {
        $article = Article::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Article deleted successfully'
                ]);

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    public function test_article_validation_works(): void
    {
        // Test missing required fields
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/articles', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'content']);

        // Test invalid author_id
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/articles', [
                            'title' => 'Test Article',
                            'content' => 'Test content',
                            'author_id' => 999
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['author_id']);

        // Test invalid division_id
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/articles', [
                            'title' => 'Test Article',
                            'content' => 'Test content',
                            'author_id' => $this->admin->id,
                            'division_id' => 999
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['division_id']);
    }

    public function test_article_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/articles/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Article not found'
                ]);
    }

    public function test_admin_cannot_update_nonexistent_article(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson('/api/articles/999', [
                            'title' => 'Updated Title'
                         ]);

        $response->assertStatus(404);
    }

    public function test_admin_cannot_delete_nonexistent_article(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson('/api/articles/999');

        $response->assertStatus(404);
    }

    public function test_only_published_articles_are_returned(): void
    {
        // Create published and unpublished articles
        Article::factory()->create(['is_published' => true]);
        Article::factory()->create(['is_published' => false]);
        Article::factory()->create(['is_published' => true]);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/articles');

        $response->assertStatus(200);
        
        // Verify only published articles are returned
        $articles = $response->json('articles.data');
        $this->assertCount(2, $articles);
        
        foreach ($articles as $article) {
            $this->assertTrue($article['is_published']);
        }
    }
} 