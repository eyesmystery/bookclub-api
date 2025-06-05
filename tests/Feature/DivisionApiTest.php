<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DivisionApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test divisions
        Division::factory()->create(['name' => 'برج القراءة', 'description' => 'Reading tower']);
        Division::factory()->create(['name' => 'برج الخبرة', 'description' => 'Experience tower']);
        
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

    public function test_guest_can_get_divisions_list(): void
    {
        $response = $this->getJson('/api/divisions');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'divisions' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ])
                ->assertJsonCount(2, 'divisions');
    }

    public function test_guest_can_get_single_division(): void
    {
        $division = Division::first();

        $response = $this->getJson("/api/divisions/{$division->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'division' => [
                        'id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'division' => [
                        'id' => $division->id,
                        'name' => $division->name,
                        'description' => $division->description
                    ]
                ]);
    }

    public function test_guest_gets_404_for_nonexistent_division(): void
    {
        $response = $this->getJson('/api/divisions/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Division not found'
                ]);
    }

    public function test_admin_can_create_division(): void
    {
        $divisionData = [
            'name' => 'برج الفلسفة',
            'description' => 'Philosophy tower for deep thinkers'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/divisions', $divisionData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'division' => [
                        'id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Division created successfully',
                    'division' => [
                        'name' => 'برج الفلسفة',
                        'description' => 'Philosophy tower for deep thinkers'
                    ]
                ]);

        $this->assertDatabaseHas('divisions', [
            'name' => 'برج الفلسفة',
            'description' => 'Philosophy tower for deep thinkers'
        ]);
    }

    public function test_regular_user_cannot_create_division(): void
    {
        $divisionData = [
            'name' => 'برج الفلسفة',
            'description' => 'Philosophy tower'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->postJson('/api/divisions', $divisionData);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_create_division(): void
    {
        $divisionData = [
            'name' => 'برج الفلسفة',
            'description' => 'Philosophy tower'
        ];

        $response = $this->postJson('/api/divisions', $divisionData);

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_admin_can_update_division(): void
    {
        $division = Division::first();
        
        $updateData = [
            'name' => 'Updated Division Name',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson("/api/divisions/{$division->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'division' => [
                        'id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Division updated successfully',
                    'division' => [
                        'id' => $division->id,
                        'name' => 'Updated Division Name',
                        'description' => 'Updated description'
                    ]
                ]);

        $this->assertDatabaseHas('divisions', [
            'id' => $division->id,
            'name' => 'Updated Division Name',
            'description' => 'Updated description'
        ]);
    }

    public function test_regular_user_cannot_update_division(): void
    {
        $division = Division::first();
        
        $updateData = [
            'name' => 'Updated Division Name'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/divisions/{$division->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_division(): void
    {
        $division = Division::create([
            'name' => 'Division to Delete',
            'description' => 'This will be deleted'
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson("/api/divisions/{$division->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Division deleted successfully'
                ]);

        $this->assertDatabaseMissing('divisions', [
            'id' => $division->id
        ]);
    }

    public function test_regular_user_cannot_delete_division(): void
    {
        $division = Division::first();

        $response = $this->actingAs($this->user, 'sanctum')
                         ->deleteJson("/api/divisions/{$division->id}");

        $response->assertStatus(403);
    }

    public function test_division_validation_works(): void
    {
        // Test missing required fields
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/divisions', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);

        // Test duplicate name
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->postJson('/api/divisions', [
                            'name' => 'برج القراءة', // Already exists
                            'description' => 'Another reading tower'
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_division_update_validation_works(): void
    {
        $division1 = Division::first();
        $division2 = Division::skip(1)->first();

        // Test updating with existing name (should fail)
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson("/api/divisions/{$division1->id}", [
                            'name' => $division2->name,
                            'description' => 'Updated description'
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);
    }

    public function test_admin_cannot_delete_nonexistent_division(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson('/api/divisions/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Division not found'
                ]);
    }

    public function test_admin_cannot_update_nonexistent_division(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson('/api/divisions/999', [
                            'name' => 'Updated Name',
                            'description' => 'Updated description'
                         ]);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Division not found'
                ]);
    }
} 