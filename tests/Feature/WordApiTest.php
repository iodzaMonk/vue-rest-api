<?php

use App\Models\User;
use App\Models\Word;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

it('registers a user via the API', function () {
    $payload = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'c_password' => 'password',
    ];

    $response = $this->postJson('/api/register', $payload);

    $response
        ->assertCreated()
        ->assertJson(
            fn(AssertableJson $json) => $json
                ->where('success', true)
                ->where('message', 'User register successfully.')
                ->has(
                    'data',
                    fn(AssertableJson $json) => $json
                        ->where('name', 'Test User')
                        ->whereType('token', 'string')
                        ->etc()
                )
        );

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

it('logs in a user via the API', function () {
    $user = User::factory()->create([
        'email' => 'login@example.com',
        // factory already hashes the default password of "password"
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'login@example.com',
        'password' => 'password',
    ]);

    $response
        ->assertOk()
        ->assertJson(
            fn(AssertableJson $json) => $json
                ->where('success', true)
                ->where('message', 'User login successfully.')
                ->has(
                    'data',
                    fn(AssertableJson $json) => $json
                        ->where('name', $user->name)
                        ->whereType('token', 'string')
                        ->etc()
                )
        );
});

it('rejects unauthenticated word creation requests', function () {
    $wordData = Word::factory()->make()->toArray();

    $response = $this->postJson('/api/words', $wordData);

    $response
        ->assertUnauthorized()
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('creates a word for an authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = Word::factory()->make()->toArray();

    $response = $this->postJson('/api/words', $payload);

    $response
        ->assertCreated()
        ->assertJson([
            'success' => true,
            'message' => 'Word created successfully.',
        ])
        ->assertJsonPath('data.word', $payload['word'])
        ->assertJsonPath('data.pronunciation', $payload['pronunciation']);

    $this->assertDatabaseHas('words', [
        'word' => $payload['word'],
        'part_of_speech' => $payload['part_of_speech'],
    ]);
});

it('lists words as JSON', function () {
    Word::factory()->count(3)->create();

    $response = $this->getJson('/api/words');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Words retrieved successfully.',
        ])
        ->assertJsonPath('data.current_page', 1)
        ->assertJsonCount(3, 'data.data');
});

it('shows a single word resource', function () {
    $word = Word::factory()->create();

    $response = $this->getJson("/api/words/{$word->id}");

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Word retrieved successfully.',
        ])
        ->assertJsonPath('data.id', $word->id)
        ->assertJsonPath('data.word', $word->word);
});

it('updates a word for authenticated users', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create([
        'difficulty' => 'easy',
    ]);
    Sanctum::actingAs($user);

    $updated = Word::factory()->make([
        'difficulty' => 'hard',
    ])->toArray();

    $response = $this->putJson("/api/words/update/{$word->id}", $updated);

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Word updated successfully.',
        ])
        ->assertJsonPath('data.difficulty', 'hard')
        ->assertJsonPath('data.word', $updated['word']);

    $this->assertDatabaseHas('words', [
        'id' => $word->id,
        'word' => $updated['word'],
        'difficulty' => 'hard',
    ]);
});

it('deletes a word for authenticated users', function () {
    $user = User::factory()->create();
    $word = Word::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->deleteJson("/api/words/{$word->id}");

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Word deleted successfully.',
        ]);

    $this->assertDatabaseMissing('words', ['id' => $word->id]);
});