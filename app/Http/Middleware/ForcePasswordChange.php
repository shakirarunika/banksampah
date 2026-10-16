<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Livewire\Livewire;
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
            && ! $request->routeIs('profile')
            && ! $this->isLivewireUpdate($request)) {
            return redirect()->route('profile');
        }

        return $next($request);
    }

    // Dicocokkan ke URL endpoint-nya, bukan nama route: Livewire 3.8 ganti nama
    // route jadi "default.livewire.update" dan bikin form ganti password diblokir.
    private function isLivewireUpdate(Request $request): bool
    {
        return $request->isMethod('post')
            && $request->is(ltrim(parse_url(Livewire::getUpdateUri(), PHP_URL_PATH), '/'));
    }
}
