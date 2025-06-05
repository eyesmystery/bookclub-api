<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventApiTest extends TestCase
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

    public function test_authenticated_user_can_get_events_list(): void
    {
        Event::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/events');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'events' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'start_date',
                                'end_date',
                                'location',
                                'max_participants',
                                'division_id',
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

    public function test_unauthenticated_user_cannot_access_events(): void
    {
        $response = $this->getJson('/api/events');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_authenticated_user_can_get_single_event(): void
    {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'event' => [
                        'id',
                        'title',
                        'description',
                        'start_date',
                        'end_date',
                        'location',
                        'max_participants',
                        'division_id',
                        'created_at',
                        'updated_at',
                        'division'
                    ]
                ]);
    }

    public function test_admin_can_create_event(): void
    {
        $eventData = [
            'title' => 'Book Club Meeting',
            'description' => 'Monthly book discussion',
            'start_date' => '2024-06-15 18:00:00',
            'end_date' => '2024-06-15 20:00:00',
            'location' => 'Main Library',
            'max_participants' => 50,
            'division_id' => 1
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/events', $eventData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'event' => [
                        'id',
                        'title',
                        'description',
                        'start_date',
                        'end_date',
                        'location',
                        'max_participants',
                        'division_id'
                    ]
                ]);

        $this->assertDatabaseHas('events', [
            'title' => 'Book Club Meeting',
            'location' => 'Main Library'
        ]);
    }

    public function test_regular_user_cannot_create_event(): void
    {
        $eventData = [
            'title' => 'Book Club Meeting',
            'description' => 'Monthly book discussion'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/events', $eventData);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_event(): void
    {
        $event = Event::factory()->create();
        
        $updateData = [
            'title' => 'Updated Event Title',
            'description' => 'Updated description',
            'location' => 'Updated Location'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson("/api/events/{$event->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'event'
                ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Event Title',
            'location' => 'Updated Location'
        ]);
    }

    public function test_admin_can_delete_event(): void
    {
        $event = Event::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Event deleted successfully'
                ]);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_event_validation_works(): void
    {
        // Test missing required fields
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/events', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'description', 'start_date']);

        // Test invalid date format
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/events', [
                            'title' => 'Test Event',
                            'description' => 'Test Description',
                            'start_date' => 'invalid-date',
                            'end_date' => '2024-06-15 20:00:00'
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['start_date']);

        // Test end date before start date
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/events', [
                            'title' => 'Test Event',
                            'description' => 'Test Description',
                            'start_date' => '2024-06-15 20:00:00',
                            'end_date' => '2024-06-15 18:00:00'
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['end_date']);
    }

    public function test_event_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/events/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Event not found'
                ]);
    }

    public function test_admin_cannot_update_nonexistent_event(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson('/api/events/999', [
                            'title' => 'Updated Title'
                         ]);

        $response->assertStatus(404);
    }

    public function test_admin_cannot_delete_nonexistent_event(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson('/api/events/999');

        $response->assertStatus(404);
    }
} 