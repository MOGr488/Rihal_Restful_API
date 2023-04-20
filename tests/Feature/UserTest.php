<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function it_returns_a_collection_of_users()
    {
        $users = User::factory()->count(3)->create();

        $response = $this->get('/api/users');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'Full Name',
                        'E-Mail',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_creates_a_new_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'role' => 'user',
        ];
    
        $response = $this->post('/api/users', $userData);
    
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['Full Name' => $userData['name']])
            ->assertJsonFragment(['E-Mail' => $userData['email']]);
    }

    /** @test */
    public function it_returns_a_user()
    {
        $user = User::factory()->create();

        $response = $this->get('/api/users/' . $user->id);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['Full Name' => $user->name])
            ->assertJsonFragment(['E-Mail' => $user->email]);
    }

    /** @test */
    public function it_updates_a_user()
    {
        $user = User::factory()->create();
        $newUserData = User::factory()->make()->toArray();

        $response = $this->put('/api/users/' . $user->id, $newUserData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonFragment(['Full Name' => $newUserData['name']])
            ->assertJsonFragment(['E-Mail' => $newUserData['email']]);
    }

    /** @test */
    public function it_deletes_a_user()
    {
        $user = User::factory()->create();

        $response = $this->delete('/api/users/' . $user->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

}