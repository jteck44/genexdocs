<?php

use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a director to delete an expert without reports', function () {
    $director = User::factory()->create(['role' => 'director']);
    $expert = User::factory()->create(['role' => 'expert']);

    $this->actingAs($director)
        ->delete(route('users.destroy', $expert))
        ->assertRedirect(route('users.index'));

    $this->assertSoftDeleted('users', ['id' => $expert->id]);
});

it('forbids an expert from deleting a team member', function () {
    $expert = User::factory()->create(['role' => 'expert']);
    $otherExpert = User::factory()->create(['role' => 'expert']);

    $this->actingAs($expert)
        ->delete(route('users.destroy', $otherExpert))
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $otherExpert->id]);
});

it('masks an expert who owns reports without deleting the reports', function () {
    $director = User::factory()->create(['role' => 'director']);
    $expert = User::factory()->create(['role' => 'expert']);
    $category = ReportCategory::create([
        'name' => 'Tests',
        'slug' => 'tests',
    ]);
    $reportType = ReportType::create([
        'report_category_id' => $category->id,
        'name' => 'Template de test',
        'slug' => 'template-de-test',
        'template_path' => 'report_templates/test.docx',
        'fields' => [],
    ]);

    Report::create([
        'report_type_id' => $reportType->id,
        'author_id' => $expert->id,
        'title' => 'Rapport de test',
        'data' => [],
        'status' => 'draft',
    ]);

    $this->actingAs($director)
        ->delete(route('users.destroy', $expert))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('status');

    $this->assertSoftDeleted('users', ['id' => $expert->id]);
    $this->assertDatabaseHas('reports', ['author_id' => $expert->id]);
});