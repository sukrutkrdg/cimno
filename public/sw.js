const CACHE_NAME = 'enerjik-cimnastik-pwa-v2';

// Yalnızca gerçekten statik, kişiye özel olmayan varlıklar önbelleğe alınır ve
// önceden yüklenir. Önceki sürüm TÜM GET isteklerini (veli bakiyesi, yoklama,
// gelişim karnesi gibi oturuma özel /mobil ve /qr-yoklama sayfaları dâhil)
// önbelleğe alıyordu; paylaşılan bir aile cihazında bir velinin verisi, cihaz
// çevrimdışıyken başka bir kullanıcıya servis edilebiliyordu. Artık /mobil,
// /qr-yoklama, /panel/* ve /makbuz/* hiçbir zaman önbelleğe yazılmaz.
const PRECACHE_URLS = [
  '/',
  '/manifest.json',
  '/icons/icon.svg',
];

function isCacheableStaticAsset(url) {
  const sameOrigin = url.origin === self.location.origin;
  if (!sameOrigin) return false;
  // Kişiye/oturuma özel tüm yollar önbellek dışı bırakılır.
  if (/^\/(mobil|qr-yoklama|panel|makbuz)(\/|$)/.test(url.pathname)) return false;
  // Sadece statik varlık uzantıları önbelleğe alınır.
  return /\.(css|js|svg|png|jpg|jpeg|webp|ico|woff2?)$/.test(url.pathname)
    || url.pathname === '/manifest.json';
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_URLS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);
  if (!isCacheableStaticAsset(url)) {
    // Kişiye özel/dinamik sayfalar: doğrudan ağdan, önbellek okuma/yazma yok.
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        const clone = response.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});
