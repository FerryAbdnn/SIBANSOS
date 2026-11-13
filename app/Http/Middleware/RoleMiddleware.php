<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        string $role
    ): Response {
        $user = $request->user();

        abort_unless(
            $user && $user->peran === $role,
            403,
            'Anda tidak memiliki hak akses.'
        );

        abort_if(
            !$user->aktif,
            403,
            'Akun Anda tidak aktif.'
        );

        if ($role === 'admin') {
            abort_if(
                !$user->id_wilayah,
                403,
                'Admin belum memiliki wilayah tugas.'
            );
        }

        return $next($request);
    }
}