<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $temporaryPassword = Str::random(12);

            try {
                Mail::to($user->email)->send(new TemporaryPasswordMail(
                    $user->name,
                    $temporaryPassword,
                ));

                $user->update([
                    'password' => $temporaryPassword,
                    'temporary_password_expires_at' => now()->addHour(),
                ]);
            } catch (TransportExceptionInterface $exception) {
                Log::error('Impossible d’envoyer le mot de passe temporaire.', [
                    'user_id' => $user->id,
                    'exception' => $exception->getMessage(),
                ]);

                return back()->withInput()->withErrors([
                    'email' => 'Le service email est momentanément indisponible. Vérifiez la configuration SMTP puis réessayez.',
                ]);
            }
        }

        return back()->with('status',
            'Si cette adresse correspond à un compte, un mot de passe temporaire vient d’être envoyé par email. Il est valable une heure.'
        );
    }
}
