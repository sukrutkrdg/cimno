<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    /**
     * Rol/izin bazlı merkezi yetkilendirme.
     *
     * Kullanım:
     *   ->middleware('admin.permission:role:super_admin')  -> yalnızca bu rol
     *   ->middleware('admin.permission:collect_payment')   -> User::hasPermission() üzerinden kontrol
     *
     * EnsureAdminAuthenticated bu middleware'den önce çalışmış ve request'e
     * 'authStaff' attribute'unu set etmiş olmalıdır.
     */
    public function handle(Request $request, Closure $next, string $requirement): Response
    {
        $user = $request->attributes->get('authStaff');

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'Bu işlem için giriş yapmanız gerekiyor.');
        }

        $authorized = str_starts_with($requirement, 'role:')
            ? $user->role === substr($requirement, 5)
            : $user->hasPermission($requirement);

        if (!$authorized) {
            return redirect()->route('admin.index')->with('error', 'Bu işlemi gerçekleştirmek için yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}
