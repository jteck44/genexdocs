<?php

use App\Models\Mandant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a director to delete a mandant', function () {
    $director = User::factory()->create(['role' => 'director']);
    $mandant = Mandant::create(['name' => 'Mandant à supprimer']);

    $response = $this->actingAs($director)
        ->delete(route('mandants.destroy', $mandant));

    $response->assertRedirect(route('mandants.index'));
    $this->assertDatabaseMissing('mandants', ['id' => $mandant->id]);
});

it('forbids an expert from deleting a mandant', function () {
    $expert = User::factory()->create(['role' => 'expert']);
    $mandant = Mandant::create(['name' => 'Mandant protégé']);

    $this->actingAs($expert)
        ->delete(route('mandants.destroy', $mandant))
        ->assertForbidden();

    $this->assertDatabaseHas('mandants', ['id' => $mandant->id]);
});

it('forbids a director adjoint from deleting a mandant', function () {
    $adjoint = User::factory()->create(['role' => 'director_adjoint']);
    $mandant = Mandant::create(['name' => 'Mandant protégé par le rôle']);

    $this->actingAs($adjoint)
        ->delete(route('mandants.destroy', $mandant))
        ->assertForbidden();

    $this->assertDatabaseHas('mandants', ['id' => $mandant->id]);
});