<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    /**
     * Tüm /panel rotaları için tek, merkezi oturum kontrolü.
     * Daha önce her controller metodu kendi kontrolünü yapıyordu (ya da hiç yapmıyordu);
     * artık giriş kontrolü burada ve route seviyesinde garanti ediliyor.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('admin_user_id');
        $user = $userId
            ? User::whereIn('role', ['super_admin', 'secretary', 'coach'])
                ->where('is_active', true)
                ->find($userId)
            : null;

        if (!$user) {
            session()->forget(['admin_user_id', 'admin_role']);

            return redirect()
                ->route('admin.login')
                ->with('error', 'Oturumunuz sona erdi veya geçersiz, lütfen tekrar giriş yapın.');
        }

        $request->attributes->set('authStaff', $user);

        return $next($request);
    }
}
