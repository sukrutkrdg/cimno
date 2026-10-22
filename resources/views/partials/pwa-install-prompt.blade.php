<!-- Otomatik "Ana Ekrana Ekle" Banner'ı + iOS/Android Kısayol Rehberi -->
<!-- Yalnızca telefonda, kurulu değilse ve yakın zamanda kapatılmadıysa kendiliğinden belirir. -->
<div id="pwa-install-banner" class="fixed inset-x-0 bottom-20 z-[70] hidden p-3">
    <div class="max-w-lg mx-auto bg-indigo-950 text-white rounded-2xl shadow-2xl p-3.5 flex items-center gap-3 border border-white/10">
        <img src="/icons/logo.jpg" alt="Enerjik Cimnastik" class="w-11 h-11 rounded-full object-cover border-2 border-purple-400 shrink-0">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-extrabold leading-tight">📲 Ana Ekranınıza Ekleyin</p>
            <p class="text-[11px] text-indigo-200 leading-tight mt-0.5">Mağazasız, uygulama gibi tek dokunuşla açın.</p>
        </div>
        <button type="button" onclick="enerjikPwa.install()" class="shrink-0 px-3 py-2 rounded-xl bg-amber-400 text-slate-950 font-extrabold text-xs">
            Yükle
        </button>
        <button type="button" onclick="enerjikPwa.dismiss()" class="shrink-0 text-indigo-300 font-extrabold text-sm px-1" aria-label="Kapat">
            ✕
        </button>
    </div>
</div>

<!-- Kalıcı Küçük Kısayol Düğmesi: banner kapatılsa/zamanı geçse bile her zaman tek dokunuşla erişim sağlar. -->
<button type="button" id="pwa-install-fab" onclick="enerjikPwa.install()"
    class="fixed bottom-20 right-3 z-[65] hidden w-12 h-12 rounded-full bg-indigo-600 text-white text-xl shadow-xl border-2 border-white items-center justify-center"
    aria-label="Ana Ekrana Ekle">
    📲
</button>

<!-- iOS & Android Ana Ekrana Ekleme Rehberi Modalı -->
<div id="install-modal" class="fixed inset-0 bg-slate-950/75 z-[80] hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl text-slate-900 dark:text-slate-100">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-extrabold">📲 Telefon Ana Ekranına Kısayol Ekle</h3>
            <button type="button" onclick="enerjikPwa.closeModal()" class="text-slate-400 font-extrabold text-lg">✕</button>
        </div>
        <p class="text-xs text-slate-600 dark:text-slate-400">Mağaza indirmesi gerekmeden uygulamamızı 5 saniyede telefon ekranınıza ekleyebilirsiniz:</p>

        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 space-y-1.5 text-xs">
            <div class="font-extrabold text-indigo-900 dark:text-indigo-300">🍎 iPhone / iPad (Safari) İçin:</div>
            <p>1. Ekranın altındaki <strong>Paylaş (Yukarı Oklu Kare ⬆️)</strong> simgesine dokunun.</p>
            <p>2. Menüden <strong>"Ana Ekrana Ekle"</strong> seçeneğine basıp sağ üstten <strong>Ekle</strong> deyin.</p>
        </div>

        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 space-y-1.5 text-xs">
            <div class="font-extrabold text-emerald-900 dark:text-emerald-300">🤖 Android (Chrome) İçin:</div>
            <p>1. Sağ üstteki <strong>Üç Nokta (⋮)</strong> menüsüne dokunun.</p>
            <p>2. <strong>"Ana ekrana ekle"</strong> veya <strong>"Uygulamayı yükle"</strong> seçeneğine dokunun.</p>
        </div>

        <button type="button" onclick="enerjikPwa.closeModal()" class="w-full py-3 rounded-xl bg-indigo-600 text-white font-extrabold text-xs">
            Anladım, Teşekkürler
        </button>
    </div>
</div>

<script>
    window.enerjikPwa = (function () {
        const DISMISS_KEY = 'enerjik_pwa_prompt_dismissed_at';
        const DISMISS_DAYS = 3;
        let deferredPrompt = null;

        function isStandalone() {
            return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        }

        function isMobile() {
            return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        }

        function recentlyDismissed() {
            try {
                const ts = parseInt(localStorage.getItem(DISMISS_KEY) || '0', 10);
                return ts > 0 && (Date.now() - ts) < DISMISS_DAYS * 24 * 60 * 60 * 1000;
            } catch (e) {
                return false;
            }
        }

        function markDismissed() {
            try { localStorage.setItem(DISMISS_KEY, String(Date.now())); } catch (e) {}
        }

        function showBanner() {
            const el = document.getElementById('pwa-install-banner');
            if (el) { el.classList.remove('hidden'); }
        }

        function hideBanner() {
            const el = document.getElementById('pwa-install-banner');
            if (el) { el.classList.add('hidden'); }
        }

        function showFab() {
            const el = document.getElementById('pwa-install-fab');
            if (el) { el.classList.remove('hidden'); el.classList.add('flex'); }
        }

        function hideFab() {
            const el = document.getElementById('pwa-install-fab');
            if (el) { el.classList.add('hidden'); el.classList.remove('flex'); }
        }

        function openModal() {
            const m = document.getElementById('install-modal');
            if (!m) return;
            m.classList.remove('hidden');
            m.classList.add('flex');
        }

        function closeModal() {
            const m = document.getElementById('install-modal');
            if (!m) return;
            m.classList.add('hidden');
            m.classList.remove('flex');
            markDismissed();
            hideBanner();
            showFab();
        }

        function install() {
            hideBanner();
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.finally(() => { deferredPrompt = null; });
                markDismissed();
            } else {
                openModal();
            }
        }

        function dismiss() {
            hideBanner();
            markDismissed();
            // Banner kapatılsa da küçük düğme ekranda kalır; kullanıcı istediği an kuruluma dönebilir.
            showFab();
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        window.addEventListener('appinstalled', () => {
            hideBanner();
            hideFab();
            markDismissed();
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (isStandalone() || !isMobile()) return;
            // Kalıcı kısayol düğmesi: kuruluş isteği kapatılsa/ertelense bile her zaman erişilebilir.
            showFab();
            if (!recentlyDismissed()) {
                setTimeout(showBanner, 1800);
            }
        });

        return { install, dismiss, openModal, closeModal };
    })();
</script>
