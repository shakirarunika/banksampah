<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang masih pakai password default (NIK) cuma boleh buka halaman profil
 * sampai password-nya diganti. Request Livewire tetap lolos biar form ganti
 * password & tombol logout jalan.
 */
class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password
            && ! $request->routeIs('profile', 'livewire.*', 'logout')) {
            return redirect()->route('profile');
        }

        return $next($request);
    }
}
