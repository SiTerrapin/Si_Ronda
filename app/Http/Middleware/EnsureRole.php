<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::guard('petugas')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // super_admin memiliki seluruh akses manajemen yang dimiliki admin + rute super_admin
        if ($user->isSuperAdmin() || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Halaman ini khusus untuk ' . implode(' / ', $roles) . '.');
    }
}
