<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('stores a generated account password in a loginable form', function () {
    $director = User::factory()->create(['role' => 'director']);

    $response = $this->actingAs($director)
        ->post(route('users.store'), [
            'name' => 'Jordan',
            'email' => 'jordan@example.com',
            'role' => 'expert',
        ])
        ->assertRedirect(route('users.index'));

    $jordan = User::where('email', 'jordan@example.com')->firstOrFail();
    preg_match('/Mot de passe temporaire : ([A-Za-z0-9]{12})/', $response->getSession()->get('status'), $matches);

    expect($matches[1] ?? null)->not->toBeNull();
    expect(Hash::check($matches[1], $jordan->getRawOriginal('password')))->toBeTrue();
});

it('lets a director reset an expert password', function () {
    $director = User::factory()->create(['role' => 'director']);
    $expert = User::factory()->create(['role' => 'expert']);

    $response = $this->actingAs($director)
        ->post(route('users.reset-password', $expert))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('status');

    $expert->refresh();
    preg_match('/ : ([A-Za-z0-9]{12})$/', $response->getSession()->get('status'), $matches);

    expect(Hash::check($matches[1], $expert->getRawOriginal('password')))->toBeTrue();
});