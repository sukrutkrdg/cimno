<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\AuditLog;
use App\Models\DiscountRule;
use App\Models\Package;
use App\Models\Student;
use Illuminate\Http\Request;

trait ManagesPackagesAndDiscounts
{
    // MODÜL 7: Paket Fiyatlarını Toplu Güncelleme (Kabul Kriteri #3)
    public function bulkUpdatePackagePrices(Request $request)
    {
        $percentage = (float) $request->input('percentage', 0);
        $fixedAdd = (float) $request->input('fixed_amount', 0);

        foreach (Package::all() as $pkg) {
            $newPrice = $pkg->price;
            if ($percentage != 0) {
                $newPrice = round($newPrice * (1 + ($percentage / 100)), -1);
            }
            if ($fixedAdd != 0) {
                $newPrice += $fixedAdd;
            }
            $pkg->update(['price' => max(0, $newPrice)]);
        }

        AuditLog::record('toplu_fiyat_guncelleme', "Paket fiyatları toplu güncellendi (Oran: %{$percentage}, Ek Tutar: {$fixedAdd} TL).");

        return redirect()->route('admin.index', ['tab' => 'paketler'])->with('success', 'Tüm paket fiyatları toplu olarak güncellendi ve ön yüz web sitesine yansıtıldı!');
    }

    // MODÜL 7: İndirim Kuralı Ekle & Düzenle
    public function storeDiscountRule(Request $request)
    {
        $rule = DiscountRule::create([
            'name' => $request->input('name', 'Yeni İndirim Kuralı'),
            'type' => $request->input('type', 'custom'),
            'percentage' => (float) $request->input('percentage', 10),
            'fixed_amount' => (float) $request->input('fixed_amount', 0),
            'is_active' => true,
        ]);
        AuditLog::record('indirim_eklendi', "Yeni indirim kuralı eklendi: {$rule->name} (%{$rule->percentage})", 'DiscountRule', $rule->id);
        $tab = $request->input('return_tab', 'paketler');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', "'{$rule->name}' indirim kuralı eklendi!");
    }

    public function updateDiscountRule(Request $request, DiscountRule $discountRule)
    {
        $request->validate([
            'percentage' => 'nullable|numeric|min:0|max:100',
            'fixed_amount' => 'nullable|numeric|min:0',
        ]);
        $discountRule->update([
            'name' => $request->input('name', $discountRule->name),
            'percentage' => (float) $request->input('percentage', $discountRule->percentage),
            'fixed_amount' => (float) $request->input('fixed_amount', $discountRule->fixed_amount),
        ]);
        AuditLog::record('indirim_guncellendi', "İndirim kuralı güncellendi: {$discountRule->name}", 'DiscountRule', $discountRule->id);
        return redirect()->route('admin.index', ['tab' => 'paketler'])->with('success', "'{$discountRule->name}' indirim kuralı güncellendi!");
    }

    public function destroyDiscountRule(DiscountRule $discountRule)
    {
        $name = $discountRule->name;
        Student::where('discount_rule_id', $discountRule->id)->update(['discount_rule_id' => null]);
        $discountRule->delete();
        AuditLog::record('indirim_silindi', "İndirim kuralı silindi: {$name}");
        return redirect()->route('admin.index', ['tab' => 'paketler'])->with('success', "'{$name}' indirim kuralı silindi.");
    }

    // CMS & Modül 7: Eğitim Paketi & Ücret Kartı Ekle / Güncelle / Sil
    public function storePackage(Request $request)
    {
        $request->validate([
            'billing_cycle' => 'nullable|in:monthly,quarterly,lesson_pack,event',
            'lessons_count' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'registration_fee' => 'nullable|numeric|min:0',
        ]);
        $pkg = Package::create([
            'name' => $request->input('name', 'Yeni Eğitim Paketi'),
            'billing_cycle' => $request->input('billing_cycle', 'monthly'),
            'lessons_count' => (int) $request->input('lessons_count', 8),
            'price' => (float) $request->input('price', 3000),
            'registration_fee' => (float) $request->input('registration_fee', 0),
            'description' => $request->input('description', ''),
            'is_active' => true,
        ]);
        AuditLog::record('paket_eklendi', "Yeni eğitim paketi eklendi: {$pkg->name} ({$pkg->price} TL)");
        $tab = $request->input('return_tab', 'site_icerigi');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', "'{$pkg->name}' paketi başarıyla eklendi!");
    }

    public function updatePackage(Request $request, Package $package)
    {
        $request->validate([
            'billing_cycle' => 'nullable|in:monthly,quarterly,lesson_pack,event',
            'lessons_count' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'registration_fee' => 'nullable|numeric|min:0',
        ]);
        $package->update([
            'name' => $request->input('name', $package->name),
            'billing_cycle' => $request->input('billing_cycle', $package->billing_cycle),
            'lessons_count' => (int) $request->input('lessons_count', $package->lessons_count),
            'price' => (float) $request->input('price', $package->price),
            'registration_fee' => (float) $request->input('registration_fee', $package->registration_fee ?? 0),
            'description' => $request->input('description', $package->description),
        ]);
        AuditLog::record('paket_guncellendi', "'{$package->name}' paket ücreti/içeriği güncellendi.");
        $tab = $request->input('return_tab', 'site_icerigi');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', "'{$package->name}' paketi güncellendi!");
    }

    public function destroyPackage(Request $request, Package $package)
    {
        $name = $package->name;
        Student::where('package_id', $package->id)->update(['package_id' => null]);
        $package->delete();
        AuditLog::record('paket_silindi', "'{$name}' paketi silindi.");
        $tab = $request->input('return_tab', 'site_icerigi');
        return redirect()->route('admin.index', ['tab' => $tab])->with('success', "'{$name}' paketi silindi.");
    }
}
