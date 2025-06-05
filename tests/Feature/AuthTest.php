<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create divisions for testing
        Division::factory()->create(['name' => 'برج القراءة']);
        Division::factory()->create(['name' => 'برج الخبرة']);
        Division::factory()->create(['name' => 'برج الفلسفة']);
        Division::factory()->create(['name' => 'برج السينما']);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'division_id' => 1,
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'division_id',
                        'role',
                        'division'
                    ],
                    'token'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'division_id' => 1,
            'role' => 'member'
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'division_id' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'division_id',
                        'role',
                        'division'
                    ],
                    'token'
                ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'division_id' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'message',
                    'errors'
                ]);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create(['division_id' => 1]);
        
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'division_id',
                        'role',
                        'division'
                    ]
                ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['division_id' => 1]);
        $token = $user->createToken('auth_token')->plainTextToken;
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Logged out successfully'
                ]);
    }

    public function test_registration_validation_works(): void
    {
        // Test missing required fields
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password', 'division_id']);

        // Test invalid email
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'division_id' => 1,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Test password confirmation mismatch
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'division_id' => 1,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }
}
