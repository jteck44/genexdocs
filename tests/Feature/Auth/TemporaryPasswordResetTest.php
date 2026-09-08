<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('emails a new one-hour temporary password', function () {
    Mail::fake();
    $user = User::factory()->create();

    $response = $this->post(route('password.email'), ['email' => $user->email]);

    $response->assertRedirect();
    $response->assertSessionHas('status');
    $user->refresh();

    expect($user->temporary_password_expires_at)->not->toBeNull();
    expect($user->temporary_password_expires_at->between(now()->addMinutes(59), now()->addMinutes(61)))->toBeTrue();
    Mail::assertSent(\App\Mail\TemporaryPasswordMail::class, fn ($mail) => $mail->hasTo($user->email)
        && Hash::check($mail->temporaryPassword, $user->getRawOriginal('password')));
});