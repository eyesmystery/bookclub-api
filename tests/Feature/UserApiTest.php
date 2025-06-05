<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create divisions for testing
        Division::factory()->create(['name' => 'برج القراءة']);
        Division::factory()->create(['name' => 'برج الخبرة']);
        
        // Create test users
        $this->user = User::factory()->create([
            'division_id' => 1,
            'role' => 'user'
        ]);
        
        $this->admin = User::factory()->create([
            'division_id' => 1,
            'role' => 'admin'
        ]);

        $this->otherUser = User::factory()->create([
            'division_id' => 2,
            'role' => 'user'
        ]);
    }

    public function test_user_can_view_their_own_profile(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson("/api/users/{$this->user->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'division_id',
                        'role',
                        'created_at',
                        'updated_at',
                        'division'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'user' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email
                    ]
                ]);
    }

    public function test_user_can_view_other_user_profile(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson("/api/users/{$this->otherUser->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'division_id',
                        'role',
                        'created_at',
                        'updated_at',
                        'division'
                    ]
                ]);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson("/api/users/{$this->user->id}");

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_user_can_update_their_own_profile(): void
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'division_id' => 2
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/users/{$this->user->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'division_id',
                        'role'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'user' => [
                        'name' => 'Updated Name',
                        'email' => 'updated@example.com',
                        'division_id' => 2
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'division_id' => 2
        ]);
    }

    public function test_user_cannot_update_other_user_profile(): void
    {
        $updateData = [
            'name' => 'Hacked Name'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/users/{$this->otherUser->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_admin_can_get_all_users(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->getJson('/api/users');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'users' => [
                        'data' => [
                            '*' => [
                                'id',
                                'name',
                                'email',
                                'division_id',
                                'role',
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

        // Should include at least 3 users (user, admin, otherUser)
        $users = $response->json('users.data');
        $this->assertGreaterThanOrEqual(3, count($users));
    }

    public function test_regular_user_cannot_get_all_users(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_user(): void
    {
        $userToDelete = User::factory()->create(['division_id' => 1]);

        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson("/api/users/{$userToDelete->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);

        $this->assertDatabaseMissing('users', ['id' => $userToDelete->id]);
    }

    public function test_regular_user_cannot_delete_user(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->deleteJson("/api/users/{$this->otherUser->id}");

        $response->assertStatus(403);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson("/api/users/{$this->admin->id}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'You cannot delete yourself'
                ]);
    }

    public function test_user_profile_not_found_returns_404(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
                         ->getJson('/api/users/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'User not found'
                ]);
    }

    public function test_user_update_validation_works(): void
    {
        // Test invalid email format
        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/users/{$this->user->id}", [
                            'email' => 'invalid-email'
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Test duplicate email
        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/users/{$this->user->id}", [
                            'email' => $this->otherUser->email
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Test invalid division_id
        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/users/{$this->user->id}", [
                            'division_id' => 999
                         ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['division_id']);
    }

    public function test_user_can_update_password(): void
    {
        $updateData = [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/users/{$this->user->id}", $updateData);

        $response->assertStatus(200);

        // Verify password was changed by attempting login with new password
        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => 'newpassword123'
        ]);

        $loginResponse->assertStatus(200);
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $updateData = [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/users/{$this->user->id}", $updateData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_password']);
    }

    public function test_user_cannot_update_role(): void
    {
        $updateData = [
            'role' => 'admin'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
                         ->putJson("/api/users/{$this->user->id}", $updateData);

        $response->assertStatus(200);

        // Role should not be updated
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'role' => 'user' // Should remain unchanged
        ]);
    }

    public function test_admin_cannot_update_nonexistent_user(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->putJson('/api/users/999', [
                            'name' => 'Updated Name'
                         ]);

        $response->assertStatus(404);
    }

    public function test_admin_cannot_delete_nonexistent_user(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
                         ->deleteJson('/api/users/999');

        $response->assertStatus(404);
    }
} 