<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    private function authorizeChiefDirector(): void
    {
        if (! Auth::user()->isChiefDirector()) {
            abort(403, "Seul le directeur en titre peut accéder à ces réglages.");
        }
    }

    public function index()
    {
        $this->authorizeChiefDirector();

        $adjoint = User::where('role', 'director_adjoint')->first();
        $experts = User::where('role', 'expert')->orderBy('name')->get();

        return view('settings.index', compact('adjoint', 'experts'));
    }

    public function setAdjoint(Request $request)
    {
        $this->authorizeChiefDirector();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $newAdjoint = User::findOrFail($validated['user_id']);

        if ($newAdjoint->id === Auth::id()) {
            return back()->withErrors(['user_id' => "Vous êtes déjà directeur."]);
        }

        User::where('role', 'director_adjoint')->update(['role' => 'expert']);

        $newAdjoint->update(['role' => 'director_adjoint']);

        return redirect()->route('settings.index')
            ->with('status', "{$newAdjoint->name} est maintenant directeur adjoint.");
    }

    public function removeAdjoint()
    {
        $this->authorizeChiefDirector();

        $adjoint = User::where('role', 'director_adjoint')->first();

        if (! $adjoint) {
            return back()->withErrors(['adjoint' => "Il n'y a actuellement aucun directeur adjoint."]);
        }

        $adjoint->update(['role' => 'expert']);

        return redirect()->route('settings.index')
            ->with('status', "{$adjoint->name} n'est plus directeur adjoint.");
    }
}