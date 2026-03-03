<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscribeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function push_subscribe_requires_authentication(): void
    {
        $response = $this->postJson(route('push.subscribe'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
            'public_key' => 'key',
            'auth_token' => 'token',
            'content_encoding' => 'aes128gcm',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function push_subscribe_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('push.subscribe'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['endpoint', 'public_key', 'auth_token', 'content_encoding']);
    }

    /** @test */
    public function push_subscribe_accepts_valid_payload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('push.subscribe'), [
                'endpoint' => 'https://fcm.googleapis.com/fcm/send/test',
                'public_key' => 'BPx' . str_repeat('a', 65),
                'auth_token' => 'auth_token_value',
                'content_encoding' => 'aes128gcm',
            ]);

        $response->assertOk();
        $response->assertJson(['status' => 'subscribed']);
    }
}
