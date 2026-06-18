<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_get_all_clients()
    {
        Client::factory()->count(5)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/clients');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_can_create_client()
    {
        $data = [
            'name' => 'Test Client',
            'email' => 'client@example.com',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'city' => 'Test City',
            'country' => 'Test Country',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/clients', $data);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('clients', ['email' => 'client@example.com']);
    }

    public function test_can_get_client_by_id()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $client->id);
    }

    public function test_can_update_client()
    {
        $client = Client::factory()->create();
        $data = ['name' => 'Updated Name'];

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/clients/{$client->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_client()
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/clients/{$client->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }
}
