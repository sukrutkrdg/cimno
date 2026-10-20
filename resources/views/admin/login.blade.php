<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici & Personel Girişi | Tekirdağ Enerjik Cimnastik</title>
    <link rel="icon" type="image/jpeg" href="/icons/logo.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Yönetim paneli teması: tek sakin aksan (teal/lacivert). Durum renkleri
        // (emerald/amber/rose) değiştirilmedi — sadece "indigo/purple" marka
        // rengi, dünyadaki kulüp yazılımlarındaki (iClassPro, Gymdesk) sakin
        // paletle uyumlu hâle getirildi.
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        indigo: {
                            50: '#f0fdfa', 100: '#ccfbf1', 200: '#99f6e4', 300: '#5eead4',
                            400: '#2dd4bf', 500: '#14b8a6', 600: '#0d9488', 700: '#0f766e',
                            800: '#115e59', 900: '#134e4a', 950: '#042f2e',
                        },
                        purple: {
                            50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
                            400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155',
                            800: '#1e293b', 900: '#0f172a', 950: '#020617',
                        },
                    },
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 min-h-screen flex items-center justify-center p-4 text-slate-900">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl border border-indigo-100 p-7 space-y-6">
        <div class="text-center space-y-2">
            <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-20 h-20 rounded-full object-cover mx-auto border-4 border-purple-600 shadow-md">
            <span class="inline-block text-[11px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-indigo-100 text-indigo-800">
                Yetkili Personel & Yönetim Girişi
            </span>
            <h1 class="text-2xl font-extrabold text-slate-900">Tekirdağ Enerjik Cimnastik</h1>
            <p class="text-xs text-slate-500">Salon Sahibi (Süper Yönetici), Sekreter ve Antrenör hesaplarınızla giriş yapın.</p>
        </div>

        @if(session('error') || session('login_error'))
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
                ⚠️ {{ session('error') ?? session('login_error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
                ✅ {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1">E-posta Adresi veya Telefon Numarası</label>
                <input type="text" name="identity" id="login-identity" required placeholder="ornek@enerjikcimnastik.com veya 0532..." class="w-full rounded-2xl border-2 border-slate-200 focus:border-indigo-600 focus:outline-none px-4 py-3 text-sm font-bold">
            </div>
            <div>
                <label class="block text-xs font-extrabold text-slate-700 mb-1">Şifre</label>
                <input type="password" name="password" id="login-password" required placeholder="••••••••" class="w-full rounded-2xl border-2 border-slate-200 focus:border-indigo-600 focus:outline-none px-4 py-3 text-sm font-bold">
            </div>
            <button type="submit" class="w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-sm shadow-lg shadow-indigo-600/25 transition">
                🔐 Yönetim Paneline Giriş Yap
            </button>
        </form>

        @if(app()->environment(['local', 'testing']))
            <!-- GÜVENLİK: Bu "hızlı giriş" bloğu yalnızca local/testing ortamında görünür.
                 Gerçek şifreleri sayfa kaynağında ifşa ettiği ve tek tıkla giriş sağladığı için
                 production'da (Natro) KESİNLİKLE gösterilmemelidir. -->
            <div class="pt-4 border-t border-slate-200 space-y-2">
                <span class="block text-[11px] font-extrabold uppercase text-slate-400">⚠️ Sadece Geliştirme Ortamı: Hızlı Rol Testi</span>
                <div class="space-y-1.5 text-xs">
                    <button type="button" onclick="fillCredentials('yonetici@enerjikcimnastik.com', 'enerjik2026')" class="w-full text-left p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 flex justify-between items-center">
                        <div>
                            <strong class="block text-slate-900">👑 Süper Yönetici (Salon Sahibi)</strong>
                            <span class="text-[11px] text-slate-500">Tüm 16 modül, finans, kasa, raporlar ve yetki ayarları</span>
                        </div>
                        <span class="text-indigo-600 font-extrabold">Seç →</span>
                    </button>
                    <button type="button" onclick="fillCredentials('sekreter@enerjikcimnastik.com', 'enerjik2026')" class="w-full text-left p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 flex justify-between items-center">
                        <div>
                            <strong class="block text-slate-900">🗂️ Sekreter (Ön Büro - Ayşe Yılmaz)</strong>
                            <span class="text-[11px] text-slate-500">Kayıt, tahsilat, WhatsApp; giderleri ve kârı göremez</span>
                        </div>
                        <span class="text-indigo-600 font-extrabold">Seç →</span>
                    </button>
                    <button type="button" onclick="fillCredentials('burak@enerjikcimnastik.com', 'enerjik2026')" class="w-full text-left p-2.5 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 flex justify-between items-center">
                        <div>
                            <strong class="block text-slate-900">🎽 Antrenör (Burak Demir)</strong>
                            <span class="text-[11px] text-slate-500">Yalnız kendi grupları, yoklama ve gelişim karnesi</span>
                        </div>
                        <span class="text-indigo-600 font-extrabold">Seç →</span>
                    </button>
                </div>
            </div>
        @endif

        <div class="text-center pt-1">
            <a href="/" class="text-xs font-bold text-slate-500 hover:text-indigo-600">← Ana Sayfaya Dön</a>
        </div>
    </div>

    @if(app()->environment(['local', 'testing']))
        <script>
            function fillCredentials(email, pass) {
                document.getElementById('login-identity').value = email;
                document.getElementById('login-password').value = pass;
            }
        </script>
    @endif
</body>
</html>
