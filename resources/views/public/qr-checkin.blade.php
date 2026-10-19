<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Salon Giriş QR Yoklama | Enerjik Cimnastik</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1E1B4B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/icons/logo.jpg">
    <link rel="icon" type="image/jpeg" href="/icons/logo.jpg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen pb-24">

    <!-- Üst Başlık -->
    <header class="bg-gradient-to-r from-emerald-700 via-teal-700 to-indigo-900 text-white px-4 py-4 shadow-md sticky top-0 z-40">
        <div class="max-w-lg mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2.5">
                <img src="/icons/logo.jpg" alt="Enerjik Cimnastik Logo" class="w-10 h-10 rounded-full object-cover border-2 border-white shadow">
                <div>
                    <h1 class="font-extrabold text-sm sm:text-base leading-none">Salon Giriş QR Yoklama</h1>
                    <span class="text-[11px] text-emerald-200 font-semibold">{{ today()->format('d.m.Y') }} · Tekirdağ Enerjik Cimnastik</span>
                </div>
            </div>
            <a href="{{ route('public.mobile.pwa') }}" class="px-3 py-1.5 rounded-xl bg-white/15 text-white text-xs font-bold">
                📱 Mobil Ekran
            </a>
        </div>
    </header>

    <main class="max-w-lg mx-auto p-3.5 space-y-4">

        @if(session('checkin_success'))
            <div class="p-4 rounded-2xl bg-emerald-600 text-white shadow-lg space-y-1 animate-pulse">
                <div class="text-base font-extrabold">🎉 Yoklama Başarıyla Alındı!</div>
                <p class="text-xs text-emerald-50">{{ session('checkin_success') }}</p>
            </div>
        @endif

        <!-- Kiosk Modu: Öğrenci Kartı Okutma Alanı -->
        <div class="bg-indigo-900 rounded-2xl p-4 border border-indigo-800 shadow-lg text-white space-y-3">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-[11px] font-bold uppercase text-indigo-300">Kiosk Modu / Hızlı Giriş</span>
                    <h2 class="text-sm font-extrabold">Öğrenci QR Kartınızı Okutun</h2>
                </div>
                <span class="text-xl">📸</span>
            </div>

            <button type="button" id="start-kiosk-camera-btn" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-extrabold flex justify-center items-center gap-2 shadow">
                Kamerayı Aç ve Kartı Göster
            </button>

            <div id="kiosk-reader-container" class="hidden mt-3 rounded-xl overflow-hidden border-2 border-dashed border-indigo-400 bg-white">
                <div id="kiosk-reader" width="100%"></div>
                <button type="button" id="stop-kiosk-camera-btn" class="w-full py-2 bg-slate-100 text-slate-700 text-xs font-bold text-center">İptal Et</button>
            </div>

            <div id="kiosk-success-message" class="hidden mt-2 p-3 bg-emerald-500 rounded-xl text-xs font-bold text-center animate-pulse">
                <!-- Ajax success message here -->
            </div>
        </div>

        <div class="flex items-center gap-4 my-2">
            <div class="h-px bg-slate-300 flex-1"></div>
            <span class="text-[11px] font-bold text-slate-400 uppercase">VEYA</span>
            <div class="h-px bg-slate-300 flex-1"></div>
        </div>

        <!-- Veli Telefon Seçimi / Girişi (Tarayıcıda Hatırlanır) -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-[11px] font-bold uppercase text-emerald-700">1. Adım: Veli Telefon Numaranız</span>
                    <h2 class="text-sm font-extrabold">Kayıtlı Sporcularınızı Görüntüleyin</h2>
                </div>
                <span class="text-xl">📲</span>
            </div>

            <form method="GET" action="{{ route('public.qr.checkin') }}" class="space-y-2.5" id="phone-lookup-form">
                <div class="flex gap-2">
                    <input type="tel" name="phone" id="parent-phone-input" value="{{ $phone }}" placeholder="05XX XXX XX XX" class="flex-1 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm font-bold">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-extrabold">
                        Sporcumu Bul
                    </button>
                </div>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    <span class="text-[11px] text-slate-400">Hızlı Demo Veli Seç:</span>
                    <a href="{{ route('public.qr.checkin', ['phone' => '05332223344']) }}" class="text-[11px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">Murat Arslan (Elif & Kerem)</a>
                    <a href="{{ route('public.qr.checkin', ['phone' => '05354445566']) }}" class="text-[11px] font-bold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700">Zeynep Çelik (Defne)</a>
                </div>
            </form>
        </div>

        <!-- QR Kamera Okuyucu Alanı -->
        @if($students->isNotEmpty())
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm space-y-3">
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-[11px] font-bold uppercase text-indigo-700">2. Adım: Salon QR Kodunu Okutun</span>
                    <h2 class="text-sm font-extrabold">Hızlı Yoklama İçin Kamerayı Açın</h2>
                </div>
                <span class="text-xl">📷</span>
            </div>

            <button type="button" id="start-camera-btn" class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-extrabold flex justify-center items-center gap-2">
                📷 Kamerayı Aç ve Salondaki QR'ı Okut
            </button>

            <div id="reader-container" class="hidden mt-3 rounded-xl overflow-hidden border-2 border-dashed border-slate-300">
                <div id="reader" width="100%"></div>
                <button type="button" id="stop-camera-btn" class="w-full py-2 bg-red-100 text-red-700 text-xs font-bold text-center">İptal Et / Kamerayı Kapat</button>
            </div>
        </div>
        @endif

        <!-- Sporcu Kartları ve Tek Dokunuşla QR Yoklama Butonu -->
        <div class="space-y-3">
            @forelse($students as $st)
                @php
                    $todayAtt = $st->attendances->first();
                @endphp
                <div class="bg-white rounded-2xl border-2 {{ $todayAtt ? 'border-emerald-500' : 'border-indigo-200' }} p-4 shadow-sm space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[11px] font-bold text-indigo-600">{{ $st->branch?->name }} · {{ $st->group?->name }}</span>
                            <h3 class="text-lg font-extrabold text-slate-900">🤸 {{ $st->full_name }}</h3>
                            <p class="text-xs text-slate-500">Antrenör: {{ $st->group?->coach?->name ?? '-' }} · Telafi Hakkı: <strong>{{ $st->makeup_credits }} Ders</strong></p>
                        </div>
                        @if($todayAtt)
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold">
                                ✅ Bugün Geldi ({{ $todayAtt->updated_at->format('H:i') }})
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-extrabold">
                                ⏳ Yoklama Bekliyor
                            </span>
                        @endif
                    </div>

                    @if($todayAtt)
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 font-semibold">
                            ✔ {{ $st->full_name }} bugün saat {{ $todayAtt->updated_at->format('H:i') }} itibarıyla derse giriş yaptı olarak kaydedildi.
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 font-semibold text-center">
                            Yoklamayı onaylamak için yukarıdaki <strong>Kamerayı Aç</strong> butonuna basıp salondaki afişi okutunuz.
                            <!-- JS'in bulabilmesi için gizli input -->
                            <input type="hidden" name="student_id" value="{{ $st->id }}">
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-6 rounded-2xl bg-white border border-slate-200 text-center text-xs text-slate-500">
                    Lütfen yukarıya salonda kayıtlı cep telefonu numaranızı yazıp <strong>"Sporcumu Bul"</strong> butonuna dokunun.
                </div>
            @endforelse
        </div>

        <!-- Bugün QR ile Salona Giriş Yapan Son Sporcular -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <h3 class="text-xs font-extrabold uppercase text-slate-500 mb-2.5">🟢 Bugün Yoklaması Alınan Sporcular</h3>
            <div class="space-y-1.5">
                @forelse($todayCheckins as $chk)
                    <div class="flex justify-between items-center text-xs py-1.5 px-2.5 rounded-lg bg-slate-50">
                        <span class="font-bold">{{ $chk->student?->full_name }} <span class="text-slate-400 font-normal">({{ $chk->group?->name }})</span></span>
                        <span class="text-emerald-700 font-bold">✅ {{ $chk->updated_at->format('H:i') }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">Bugün henüz giriş kaydı yok.</p>
                @endforelse
            </div>
        </div>

    </main>

    <!-- Alt Mobil Bar -->
    <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-slate-200 z-40 shadow-lg">
        <div class="max-w-lg mx-auto grid grid-cols-4 text-center py-2">
            <a href="{{ route('public.mobile.pwa', ['rol' => 'veli', 'phone' => $phone]) }}" class="flex flex-col items-center gap-0.5 text-slate-500 font-semibold">
                <span class="text-lg">👨‍👩‍👧</span>
                <span class="text-[11px]">Veli Ekranı</span>
            </a>
            <a href="{{ route('public.qr.checkin', ['phone' => $phone]) }}" class="flex flex-col items-center gap-0.5 text-emerald-600 font-extrabold">
                <span class="text-lg">📷</span>
                <span class="text-[11px]">QR Yoklama</span>
            </a>
            <a href="{{ route('public.mobile.pwa', ['rol' => 'antrenor']) }}" class="flex flex-col items-center gap-0.5 text-slate-500 font-semibold">
                <span class="text-lg">🎽</span>
                <span class="text-[11px]">Antrenör</span>
            </a>
        </div>
    </nav>

    <script>
        // Veli telefon numarasını tarayıcı hafızasında tut, her gelişte tekrar yazmasın!
        const input = document.getElementById('parent-phone-input');
        if (input && input.value) {
            localStorage.setItem('enerjik_parent_phone', input.value);
        }

        // Kiosk Modu QR Okuma Mantığı
        const startKioskCameraBtn = document.getElementById('start-kiosk-camera-btn');
        const stopKioskCameraBtn = document.getElementById('stop-kiosk-camera-btn');
        const kioskReaderContainer = document.getElementById('kiosk-reader-container');
        const kioskSuccessMessage = document.getElementById('kiosk-success-message');
        let html5KioskQrcodeScanner = null;

        if (startKioskCameraBtn) {
            startKioskCameraBtn.addEventListener('click', function() {
                kioskReaderContainer.classList.remove('hidden');
                startKioskCameraBtn.classList.add('hidden');
                kioskSuccessMessage.classList.add('hidden');

                const html5QrCode = new Html5Qrcode("kiosk-reader");
                html5KioskQrcodeScanner = html5QrCode;

                const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                    if (decodedText.startsWith('ST-ID-')) {
                        html5QrCode.stop().then((ignore) => {
                            kioskReaderContainer.classList.add('hidden');
                            submitKioskAttendanceViaAjax(decodedText);
                        }).catch((err) => {
                            console.error("Kamera durdurulamadı.", err);
                        });
                    } else {
                        alert("Geçersiz öğrenci kartı!");
                    }
                };

                const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
                .catch((err) => {
                    alert("Kamera başlatılamadı: " + err);
                    kioskReaderContainer.classList.add('hidden');
                    startKioskCameraBtn.classList.remove('hidden');
                });
            });
        }

        if (stopKioskCameraBtn) {
            stopKioskCameraBtn.addEventListener('click', function() {
                if (html5KioskQrcodeScanner) {
                    html5KioskQrcodeScanner.stop().then((ignore) => {
                        kioskReaderContainer.classList.add('hidden');
                        startKioskCameraBtn.classList.remove('hidden');
                    });
                }
            });
        }

        function submitKioskAttendanceViaAjax(qrData) {
            const formData = new FormData();
            formData.append('_token', "{{ csrf_token() }}");
            formData.append('qr_data', qrData);

            fetch("{{ route('public.qr.checkin.kiosk') }}", {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    kioskSuccessMessage.innerHTML = '🎉 ' + data.message;
                    kioskSuccessMessage.classList.remove('hidden');
                    startKioskCameraBtn.classList.remove('hidden');
                    // 3 saniye sonra sayfayı yenile
                    setTimeout(() => window.location.reload(), 3000);
                } else {
                    alert(data.error || 'Bir hata oluştu.');
                    startKioskCameraBtn.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("İşlem sırasında bir hata oluştu.");
                startKioskCameraBtn.classList.remove('hidden');
            });
        }

        // Veli (Afiş) QR Okuma Mantığı
        const startCameraBtn = document.getElementById('start-camera-btn');
        const stopCameraBtn = document.getElementById('stop-camera-btn');
        const readerContainer = document.getElementById('reader-container');
        let html5QrcodeScanner = null;

        if (startCameraBtn) {
            startCameraBtn.addEventListener('click', function() {
                readerContainer.classList.remove('hidden');
                startCameraBtn.classList.add('hidden');

                const html5QrCode = new Html5Qrcode("reader");
                html5QrcodeScanner = html5QrCode;

                const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                    // QR kodu okundu, salon afiş URL'sini (qr-yoklama) kontrol et
                    if (decodedText.includes('qr-yoklama') || decodedText.includes('mobil')) {
                        html5QrCode.stop().then((ignore) => {
                            // Kamerayı kapat
                            readerContainer.classList.add('hidden');

                            // Sayfadaki öğrencilerin gizli inputlarını bul ve yoklamayı gönder
                            submitAttendanceViaAjax();
                        }).catch((err) => {
                            console.error("Kamera durdurulamadı.", err);
                        });
                    } else {
                        alert("Lütfen salon girişindeki doğru QR kodu okutun.");
                    }
                };

                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
                .catch((err) => {
                    alert("Kamera başlatılamadı: " + err);
                    readerContainer.classList.add('hidden');
                    startCameraBtn.classList.remove('hidden');
                });
            });
        }

        if (stopCameraBtn) {
            stopCameraBtn.addEventListener('click', function() {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.stop().then((ignore) => {
                        readerContainer.classList.add('hidden');
                        startCameraBtn.classList.remove('hidden');
                    }).catch((err) => {
                        console.error("Kamera durdurulamadı.", err);
                    });
                }
            });
        }

        function submitAttendanceViaAjax() {
            const studentInputs = document.querySelectorAll('input[name="student_id"]');
            if (studentInputs.length === 0) {
                alert("Yoklaması alınacak öğrenci bulunamadı.");
                return;
            }

            // Kullanıcıya bilgi ver
            startCameraBtn.classList.remove('hidden');
            startCameraBtn.innerText = "⏳ Yoklamalar Sisteme İşleniyor...";
            startCameraBtn.disabled = true;
            startCameraBtn.classList.remove('bg-slate-900', 'hover:bg-slate-800');
            startCameraBtn.classList.add('bg-amber-500');

            const csrfToken = "{{ csrf_token() }}";
            let completedRequests = 0;

            studentInputs.forEach(input => {
                const studentId = input.value;
                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('student_id', studentId);
                formData.append('checkin_type', 'present');

                fetch("{{ route('public.qr.checkin.submit') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json().catch(() => response))
                .then(data => {
                    completedRequests++;
                    if (completedRequests === studentInputs.length) {
                        // Tüm istekler bittiğinde sayfayı yenile
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    completedRequests++;
                    if (completedRequests === studentInputs.length) {
                        window.location.reload();
                    }
                });
            });
        }
    </script>
</body>
</html>
