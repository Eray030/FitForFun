<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Rollen
    const ROL_KLANT        = 'klant';
    const ROL_INSTRUCTEUR  = 'instructeur';
    const ROL_EIGENAAR     = 'eigenaar';

    protected $fillable = [
        'voornaam',
        'achternaam',
        'email',
        'password',
        'rol',
        'adres',
        'woonplaats',
        'geboortedatum',
        'mobiel',
        'bsn',              // alleen instructeur/eigenaar
        'actief',
        'activatie_token',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'activatie_token',
        'bsn',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'geboortedatum'     => 'date',
        'actief'            => 'boolean',
    ];

    // ── Relaties ──

    /** Reserveringen van deze klant */
    public function reserveringen()
    {
        return $this->hasMany(Reservering::class, 'klant_id');
    }

    /** Lessen die deze instructeur geeft */
    public function lessen()
    {
        return $this->hasMany(Les::class, 'instructeur_id');
    }

    // ── Helpers ──

    public function volledigeNaam(): string
    {
        return $this->voornaam . ' ' . $this->achternaam;
    }

    public function isKlant(): bool
    {
        return $this->rol === self::ROL_KLANT;
    }

    public function isInstructeur(): bool
    {
        return $this->rol === self::ROL_INSTRUCTEUR;
    }

    public function isEigenaar(): bool
    {
        return $this->rol === self::ROL_EIGENAAR;
    }

    public function dashboardRoute(): string
    {
        return match($this->rol) {
            self::ROL_KLANT        => route('klant.dashboard'),
            self::ROL_INSTRUCTEUR  => route('instructeur.dashboard'),
            self::ROL_EIGENAAR     => route('eigenaar.dashboard'),
            default                => route('home'),
        };
    }
}
