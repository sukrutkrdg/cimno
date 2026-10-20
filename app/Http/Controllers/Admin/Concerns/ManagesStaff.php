<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait ManagesStaff
{
    // MODÜL 10: Personel / Sekreter / Antrenör Hesabı Açma, Kapatma ve Silme (Kabul Kriteri #5)
    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:30',
            'role' => 'required|in:super_admin,secretary,coach',
            'specialty' => 'nullable|string|max:120',
            'compensation_type' => 'required|in:fixed,per_lesson,both,none',
            'base_salary' => 'nullable|numeric|min:0',
            'per_lesson_rate' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:6|max:60',
        ]);

        // Alan boş bırakılırsa, tüm yeni hesaplar için ortak/tahmin edilebilir sabit
        // bir şifre yerine rastgele güçlü bir şifre üretilir; admin'e ekranda gösterilir.
        $plainPassword = $request->filled('password') ? $request->input('password') : Str::password(10, true, true, false, false);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'specialty' => $validated['specialty'] ?? null,
            'compensation_type' => $validated['compensation_type'],
            'base_salary' => $validated['base_salary'] ?? 0,
            'per_lesson_rate' => $validated['per_lesson_rate'] ?? 0,
            'password' => Hash::make($plainPassword),
            'is_active' => true,
            'permissions' => [
                'collect_payment' => $validated['role'] !== 'coach',
                'view_expenses' => $validated['role'] === 'super_admin',
                'view_reports' => $validated['role'] === 'super_admin',
                'send_whatsapp' => $validated['role'] !== 'coach',
                'edit_content' => $validated['role'] !== 'coach',
                'only_own_groups' => $validated['role'] === 'coach',
                'take_attendance' => true,
                'add_development' => true,
            ],
        ]);

        AuditLog::record('personel_eklendi', "Yeni personel hesabı açıldı: {$user->name} ({$user->roleLabel()})", 'User', $user->id);

        return redirect()->route('admin.index', ['tab' => 'personel'])->with('success', "{$user->name} ({$user->roleLabel()}) hesabı başarıyla açıldı! Giriş E-postası: {$user->email} · Şifre: {$plainPassword}");
    }

    public function toggleStaffStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $durum = $user->is_active ? 'aktif edildi' : 'kapatıldı (pasife alındı)';
        AuditLog::record('personel_durum_degisti', "{$user->name} kullanıcı hesabı {$durum}.", 'User', $user->id);
        return redirect()->route('admin.index', ['tab' => 'personel'])->with('success', "{$user->name} hesabı {$durum}.");
    }

    public function destroyStaff(Request $request, User $user)
    {
        // Rol kontrolü route middleware'inde yapılır; burada sadece "kendi hesabını silme" iş kuralı kalır.
        $authUser = $this->getAuthenticatedStaff();
        if ($authUser->id === $user->id) {
            return redirect()->route('admin.index', ['tab' => 'personel'])->with('error', 'Şu anda oturum açtığınız kendi Süper Yönetici hesabınızı silemezsiniz!');
        }

        $name = $user->name;
        $roleLabel = $user->roleLabel();
        Group::where('coach_id', $user->id)->update(['coach_id' => null]);
        $user->delete();

        AuditLog::record('personel_silindi', "{$name} ({$roleLabel}) kullanıcı hesabı sistemden silindi.", 'User', $user->id);

        return redirect()->route('admin.index', ['tab' => 'personel'])->with('success', "{$name} ({$roleLabel}) kullanıcı hesabı başarıyla silindi.");
    }

    public function updateStaffPermissions(Request $request, User $user)
    {
        // Yetki kontrolü route middleware'inde (admin.permission:role:super_admin) merkezi olarak yapılır.
        $role = $request->input('role', $user->role);
        if (!in_array($role, ['super_admin', 'secretary', 'coach'], true)) {
            $role = $user->role;
        }

        $permissions = [
            'collect_payment' => $request->boolean('perm_collect_payment'),
            'view_expenses' => $request->boolean('perm_view_expenses'),
            'view_reports' => $request->boolean('perm_view_reports'),
            'send_whatsapp' => $request->boolean('perm_send_whatsapp'),
            'edit_content' => $request->boolean('perm_edit_content'),
            'delete_student' => $request->boolean('perm_delete_student'),
            'only_own_groups' => $request->boolean('perm_only_own_groups'),
            'take_attendance' => $request->boolean('perm_take_attendance'),
            'add_development' => $request->boolean('perm_add_development'),
        ];

        $updateData = [
            'role' => $role,
            'is_active' => $request->boolean('is_active', true),
            'permissions' => $permissions,
        ];

        if ($request->filled('new_password')) {
            $updateData['password'] = Hash::make($request->input('new_password'));
        }

        $user->update($updateData);

        AuditLog::record('yetki_guncellendi', "{$user->name} personelinin rol ({$user->roleLabel()}) ve erişim yetkileri güncellendi.", 'User', $user->id);

        return redirect()->route('admin.index', ['tab' => 'personel'])->with('success', "{$user->name} için rol, erişim yetkileri ve giriş ayarları başarıyla güncellendi!");
    }
}
