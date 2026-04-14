const CACHE_NAME = 'emfitpro-v1';
const assets = [
  './',
  './index.html',
  './style.css',
  './app.js',
  'https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&display=swap'
];

self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(assets))
  );
});

self.addEventListener('fetch', e => {
  e.respondWith(
    caches.match(e.request).then(response => response || fetch(e.request))
  );
});

// Push Notification Logic
self.addEventListener('push', function(event) {
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: 'https://cdn-icons-png.flaticon.com/512/3048/3048398.png',
        badge: 'https://cdn-icons-png.flaticon.com/512/3048/3048398.png',
        data: { url: data.url }
    };
    event.waitUntil(self.registration.showNotification(data.title, options));
});
