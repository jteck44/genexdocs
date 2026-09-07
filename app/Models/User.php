<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function reportsAuthored(): HasMany
    {
        return $this->hasMany(Report::class, 'author_id');
    }

    public function reportsReviewed(): HasMany
    {
        return $this->hasMany(Report::class, 'director_id');
    }

    /**
     * A les pleins droits de direction — vrai pour le directeur ET
     * l'adjoint. Tout le reste du code (import de template, validation
     * de rapport, gestion d'équipe...) utilise cette méthode : l'adjoint
     * hérite donc automatiquement des mêmes droits, sans rien changer
     * ailleurs.
     */
    public function isDirector(): bool
    {
        return in_array($this->role, ['director', 'director_adjoint'], true);
    }

    /**
     * Le directeur EN TITRE uniquement — réservé aux actions les plus
     * sensibles (désigner/retirer l'adjoint).
     */
    public function isChiefDirector(): bool
    {
        return $this->role === 'director';
    }

    public function isDirectorAdjoint(): bool
    {
        return $this->role === 'director_adjoint';
    }

    public function isExpert(): bool
    {
        return $this->role === 'expert';
    }
}