<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    private function authorizeDirector(): void
    {
        if (! Auth::user()->isDirector()) {
            abort(403, "Seul un directeur peut gérer les comptes.");
        }
    }

    public function index()
    {
        $this->authorizeDirector();

        $users = User::orderBy('name')->get();

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorizeDirector();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:expert,director',
        ]);

        $temporaryPassword = Str::random(12);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($temporaryPassword),
        ]);

        return redirect()->route('users.index')->with('status',
            "Compte créé pour {$user->name}. Mot de passe temporaire : {$temporaryPassword} "
            ."(transmettez-le-lui, il devra le changer via « Mot de passe oublié »)."
        );
    }
}