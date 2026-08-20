<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ── Registratie stap 1: formulier ──
    public function registreerForm()
    {
        return view('auth.registreer');
    }

    // ── Registratie stap 1: verstuur activatiemail ──
    public function registreer(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ], [
            'email.unique' => 'Dit e-mailadres is al geregistreerd.',
        ]);

        $token = Str::random(64);

        User::create([
            'email'           => $request->email,
            'rol'             => User::ROL_KLANT,
            'actief'          => false,
            'activatie_token' => $token,
            'password'        => '', // nog geen wachtwoord
        ]);

        // Mail::to($request->email)->send(new \App\Mail\ActivatieMail($token));
        // (Vervang bovenstaande door echte mail zodra maildriver is geconfigureerd)

        return redirect()->route('auth.activeer', $token)
            ->with('info', 'Activatiemail verstuurd naar ' . $request->email);
    }

    // ── Registratie stap 2: activatielink ──
    public function activeer(string $token)
    {
        $user = User::where('activatie_token', $token)->firstOrFail();
        return view('auth.wachtwoord', compact('token', 'user'));
    }

    // ── Registratie stap 3: wachtwoord instellen ──
    public function wachtwoordForm(string $token)
    {
        $user = User::where('activatie_token', $token)->firstOrFail();
        return view('auth.wachtwoord', compact('token', 'user'));
    }

    public function wachtwoordOpslaan(Request $request, string $token)
    {
        $request->validate([
            'password'              => [
                'required', 'confirmed', 'min:12',
                'regex:/[A-Z]/',        // minimaal 1 hoofdletter
                'regex:/[0-9]/',        // minimaal 1 cijfer
                'regex:/[@#!$%^&*]/',   // minimaal 1 leesteken
            ],
        ], [
            'password.min'     => 'Wachtwoord moet minimaal 12 tekens zijn.',
            'password.regex'   => 'Wachtwoord moet een hoofdletter, cijfer en leesteken bevatten.',
            'password.confirmed' => 'Wachtwoorden komen niet overeen.',
        ]);

        $user = User::where('activatie_token', $token)->firstOrFail();
        $user->update([
            'password'           => Hash::make($request->password),
            'actief'             => true,
            'activatie_token'    => null,
            'email_verified_at'  => now(),
        ]);

        Auth::login($user);

        return redirect($user->dashboardRoute())
            ->with('success', 'Registratie voltooid! Welkom bij Windkracht-12.');
    }

    // ── Login ──
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect(Auth::user()->dashboardRoute());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Controleer of account actief/geblokkeerd is
        if ($user && !$user->actief) {
            return back()->withErrors(['email' => 'Je account is geblokkeerd of nog niet geactiveerd.']);
        }

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('onthouden'))) {
            return back()->withErrors(['email' => 'E-mailadres of wachtwoord onjuist.'])->withInput();
        }

        // Log inloggen (tijd microseconde nauwkeurig, datum, e-mail)
        $this->logActie('inloggen', Auth::user()->email);

        $request->session()->regenerate();

        return redirect(Auth::user()->dashboardRoute());
    }

    // ── Logout ──
    public function logout(Request $request)
    {
        $this->logActie('uitloggen', Auth::user()->email);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // ── Interne helper: schrijf naar logfile ──
    private function logActie(string $actie, string $email): void
    {
        $regel = implode(' | ', [
            now()->format('Y-m-d'),
            microtime(true),
            $actie,
            $email,
        ]) . PHP_EOL;

        file_put_contents(storage_path('logs/auth.log'), $regel, FILE_APPEND | LOCK_EX);
    }
}
