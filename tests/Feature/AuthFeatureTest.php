<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    //resigter tests
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(201); // successful registration returns 201 status.
        $response->assertJsonStructure([
            'status',
            'message',
            'user' => [
                'id',
                'name',
                'email',
            ],
            'authorisation' => [
                'token',    // JWT token should be in the response
                'type'
            ]
        ]);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_registration_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['name', 'password']);
    }

    public function test_registration_fails_with_invalid_email()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_weak_password()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password', // Weak password
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['password']);
    }

    //login tests
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(structure: [
            'status',
            'user' => ['id', 'name', 'email'],
            'authorisation' => [
                'token',
                'type',
            ]
        ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(500); // Unauthorized
    }

    public function test_login_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_login_is_throttled_after_too_many_attempts()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'john@example.com',
                'password' => 'WrongPassword',
            ]);
        }

        $response->assertStatus(429); // Too many requests
    }

    //logout tests

    public function test_user_can_logout_when_authenticated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->postJson('/api/logout');

        $response->assertStatus(200); // Logout success
        $response->assertJson(['message' => 'Successfully logged out']);
    }

    public function test_logout_requires_authentication()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401); // Unauthorized
    }

    // refresh tests 
    public function test_user_can_refresh_token_when_authenticated()
    {
        // Create a user with known credentials
        $user = User::factory()->create([
            'password' => bcrypt('Password123!')
        ]);

        // Log in to get a token
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password123!'
        ]);

        $response->assertStatus(200); // Ensure login was successful
        $response->assertJsonStructure([
            'status',
            'authorisation' => [
                'token',
                'type',
            ]
        ]);

        // Retrieve the token from the login response
        $token = $response['authorisation']['token'];

        // Use the token to call the refresh endpoint
        $response2 = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->postJson('/api/refresh');

        $response2->assertStatus(200); // Ensure refresh was successful
        $response2->assertJsonStructure([
            'status',
            'authorisation' => [
                'token',
                'type',
            ]
        ]);
    }



    public function test_refresh_requires_authentication()
    {
        $response = $this->postJson('/api/refresh');

        $response->assertStatus(401); // Unauthorized
    }
}
