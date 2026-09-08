<?php

use App\Models\User;
use App\Mail\TemporaryPasswordMail;
use Illuminate\Support\Facades\Mail;

test('temporary password request screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertStatus(200);
});

test('temporary password can be requested for an existing account', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email])
        ->assertSessionHas('status');

    Mail::assertSent(TemporaryPasswordMail::class, fn ($mail) => $mail->hasTo($user->email));
});

test('unknown email does not send a temporary password', function () {
    Mail::fake();

    $this->post(route('password.email'), ['email' => 'unknown@example.com'])
        ->assertSessionHas('status');

    Mail::assertNothingSent();
});
