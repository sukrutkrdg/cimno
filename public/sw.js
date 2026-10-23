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

// Web Push: sunucudan (ders saati yaklaşıyor hatırlatması vb.) gelen bir push
// mesajını telefon bildirimi olarak gösterir. Mesaj gövdesi her zaman JSON
// {title, body, url} biçiminde gönderilir (bkz. app/Services/PushNotifier.php).
self.addEventListener('push', (event) => {
  let data = { title: 'Enerjik Cimnastik', body: 'Yeni bir bildiriminiz var.', url: '/mobil' };
  try {
    if (event.data) data = { ...data, ...event.data.json() };
  } catch (e) {}

  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/icons/icon-192.png',
      badge: '/icons/icon-192.png',
      data: { url: data.url },
      tag: data.tag || 'enerjik-bildirim',
    })
  );
});

// Bildirime dokununca ilgili sayfayı (veya zaten açık bir sekmeyi) öne getirir.
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const targetUrl = event.notification.data?.url || '/mobil';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
      for (const client of clients) {
        if (client.url.includes(targetUrl) && 'focus' in client) {
          return client.focus();
        }
      }
      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }
    })
  );
});
