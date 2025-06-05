<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsApiTest extends TestCase
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

    public function test_authenticated_user_can_get_news_list(): void
    {
        News::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/news');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'news' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'content',
                                'excerpt',
                                'division_id',
                                'featured_image',
                                'is_published',
                                'published_at',
                                'created_at',
                                'updated_at',
                                'division'
                            ]
                        ],
                        'current_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }

    public function test_unauthenticated_user_cannot_access_news(): void
    {
        $response = $this->getJson('/api/news');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_authenticated_user_can_get_single_news(): void
    {
        $news = News::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson("/api/news/{$news->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'news' => [
                        'id',
                        'title',
                        'content',
                        'excerpt',
                        'division_id',
                        'featured_image',
                        'is_published',
                        'published_at',
                        'created_at',
                        'updated_at',
                        'division'
                    ]
                ]);
    }

    public function test_admin_can_create_news(): void
    {
        $newsData = [
            'title' => 'Test News Article',
            'content' => 'This is a test news content',
            'excerpt' => 'Test news excerpt',
            'division_id' => 1,
            'is_published' => true
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/news', $newsData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'news' => [
                        'id',
                        'title',
                        'content',
                        'excerpt',
                        'division_id',
                        'is_published'
                    ]
                ]);

        $this->assertDatabaseHas('news', [
            'title' => 'Test News Article',
            'content' => 'This is a test news content'
        ]);
    }

    public function test_regular_user_cannot_create_news(): void
    {
        $newsData = [
            'title' => 'Test News Article',
            'content' => 'This is a test news content'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/news', $newsData);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_news(): void
    {
        $news = News::factory()->create();
        
        $updateData = [
            'title' => 'Updated News Title',
            'content' => 'Updated news content',
            'excerpt' => 'Updated excerpt'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson("/api/news/{$news->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'news'
                ]);

        $this->assertDatabaseHas('news', [
            'id' => $news->id,
            'title' => 'Updated News Title',
            'content' => 'Updated news content'
        ]);
    }

    public function test_admin_can_delete_news(): void
    {
        $news = News::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson("/api/news/{$news->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'News deleted successfully'
                ]);

        $this->assertDatabaseMissing('news', ['id' => $news->id]);
    }

    public function test_news_validation_works(): void
    {
        // Test missing required fields
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/news', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'content']);

        // Test invalid division_id
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/news', [
                            'title' => 'Test News',
                            'content' => 'Test content',
                            'division_id' => 999
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['division_id']);
    }

    public function test_news_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/news/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'News not found'
                ]);
    }

    public function test_admin_cannot_update_nonexistent_news(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson('/api/news/999', [
                            'title' => 'Updated Title'
                         ]);

        $response->assertStatus(404);
    }

    public function test_admin_cannot_delete_nonexistent_news(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson('/api/news/999');

        $response->assertStatus(404);
    }

    public function test_only_published_news_are_returned(): void
    {
        // Create published and unpublished news
        News::factory()->create(['is_published' => true]);
        News::factory()->create(['is_published' => false]);
        News::factory()->create(['is_published' => true]);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/news');

        $response->assertStatus(200);
        
        // Verify only published news are returned
        $news = $response->json('news.data');
        $this->assertCount(2, $news);
        
        foreach ($news as $newsItem) {
            $this->assertTrue($newsItem['is_published']);
        }
    }

    public function test_news_are_ordered_by_published_date(): void
    {
        // Create news with different published dates
        $oldNews = News::factory()->create([
            'is_published' => true,
            'published_at' => now()->subDays(2)
        ]);
        
        $newNews = News::factory()->create([
            'is_published' => true,
            'published_at' => now()
        ]);
        
        $middleNews = News::factory()->create([
            'is_published' => true,
            'published_at' => now()->subDay()
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/news');

        $response->assertStatus(200);
        
        $newsItems = $response->json('news.data');
        
        // Should be ordered by most recent first
        $this->assertEquals($newNews->id, $newsItems[0]['id']);
        $this->assertEquals($middleNews->id, $newsItems[1]['id']);
        $this->assertEquals($oldNews->id, $newsItems[2]['id']);
    }
} 