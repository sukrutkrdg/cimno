<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Yönetici Özeti | Enerjik Cimnastik</title>
    <link rel="icon" type="image/jpeg" href="/icons/logo.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4',
                            400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e',
                            800: '#115e59', 900: '#134e4a', 950: '#042f2e',
                        },
                    },
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen">

    <header class="bg-slate-900 text-white px-4 py-3.5 sticky top-0 z-10 shadow flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <img src="/icons/logo.jpg" alt="Logo" class="w-9 h-9 rounded-full object-cover border-2 border-indigo-400">
            <div>
                <div class="font-extrabold text-sm leading-none">{{ $authUser->name }}</div>
                <div class="text-[11px] text-slate-400">{{ $authUser->roleLabel() }}</div>
            </div>
        </div>
        <a href="{{ route('admin.index') }}" class="text-[11px] font-bold px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500">Tam Panel →</a>
    </header>

    <main class="p-4 space-y-4 max-w-xl mx-auto">
        <div>
            <h1 class="text-lg font-extrabold">👋 Günaydın, {{ explode(' ', $authUser->name)[0] }}</h1>
            <p class="text-xs text-slate-500">{{ now()->translatedFormat('d F Y, l') }} · Hızlı günlük özet</p>
        </div>

        @if($pendingWhatsappCount > 0 && in_array('mesajlar', $allowedTabs, true))
            <a href="{{ route('admin.index', ['tab' => 'mesajlar']) }}" class="block p-4 rounded-2xl bg-amber-400 text-slate-950 font-extrabold text-sm shadow-sm flex items-center justify-between">
                <span>💬 {{ $pendingWhatsappCount }} Bekleyen WhatsApp Mesajı</span>
                <span>→</span>
            </a>
        @endif

        <!-- Bugünkü Dersler -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2.5">
                <h2 class="font-extrabold text-sm text-indigo-900">📅 Bugünkü Dersler ({{ $todaySchedules->count() }})</h2>
                @if(in_array('takvim', $allowedTabs, true))
                    <a href="{{ route('admin.index', ['tab' => 'takvim']) }}" class="text-[11px] text-indigo-600 font-bold underline">Takvim →</a>
                @endif
            </div>
            @forelse($todaySchedules as $sch)
                <div class="flex items-center justify-between py-2 border-b last:border-b-0 border-slate-100 text-xs">
                    <div>
                        <div class="font-bold">{{ substr($sch->start_time, 0, 5) }}–{{ substr($sch->end_time, 0, 5) }} · {{ $sch->group?->name }}</div>
                        <div class="text-slate-500">{{ $sch->group?->coach?->name ?? '-' }}</div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 italic">Bugün için planlanmış ders yok.</p>
            @endforelse
        </div>

        <!-- Bugünkü Yoklama -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <h2 class="font-extrabold text-sm text-indigo-900 mb-2.5">✅ Bugünkü Yoklama</h2>
            <div class="grid grid-cols-4 gap-2 text-center">
                <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200">
                    <div class="text-lg font-extrabold text-emerald-700">{{ $todayAttendanceCounts['present'] ?? 0 }}</div>
                    <div class="text-[10px] font-bold text-emerald-700">Geldi</div>
                </div>
                <div class="p-2 rounded-xl bg-rose-50 border border-rose-200">
                    <div class="text-lg font-extrabold text-rose-700">{{ $todayAttendanceCounts['absent'] ?? 0 }}</div>
                    <div class="text-[10px] font-bold text-rose-700">Gelmedi</div>
                </div>
                <div class="p-2 rounded-xl bg-amber-50 border border-amber-200">
                    <div class="text-lg font-extrabold text-amber-700">{{ $todayAttendanceCounts['excused'] ?? 0 }}</div>
                    <div class="text-[10px] font-bold text-amber-700">Raporlu</div>
                </div>
                <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="text-lg font-extrabold text-slate-700">{{ $todayAttendanceCounts['makeup'] ?? 0 }}</div>
                    <div class="text-[10px] font-bold text-slate-700">Telafi</div>
                </div>
            </div>
        </div>

        @if($newLeadsCount !== null)
            <a href="{{ route('admin.index', ['tab' => 'adaylar']) }}" class="block bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <div class="font-extrabold text-sm text-indigo-900">🎯 Bekleyen Deneme Adayı</div>
                    <div class="text-2xl font-extrabold text-amber-600 mt-0.5">{{ $newLeadsCount }}</div>
                </div>
                <span class="text-indigo-600 font-bold text-xs">Adayları Gör →</span>
            </a>
        @endif

        @if($overdueTotal !== null)
            <a href="{{ route('admin.index', ['tab' => 'aidat']) }}" class="block bg-white rounded-2xl border border-slate-200 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <div class="font-extrabold text-sm text-indigo-900">💳 Geciken Aidat</div>
                    <div class="text-2xl font-extrabold text-rose-600 mt-0.5">{{ number_format($overdueTotal, 0, ',', '.') }} ₺</div>
                </div>
                <span class="text-indigo-600 font-bold text-xs">Aidat Sekmesi →</span>
            </a>
        @endif

        <a href="{{ route('admin.logout') }}" class="block text-center py-2.5 text-xs font-bold text-rose-600">🚪 Çıkış Yap</a>
    </main>

</body>
</html>
