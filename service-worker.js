
self.addEventListener('install', function(event) {
  
  event.waitUntil(
    
    caches.open('my-cache').then(function(cache) {
      
      return Promise.all([
        '/',
        'index.php',
        'style.css',
        'favicon/web-app-manifest-192x192.png',
        'favicon/web-app-manifest-512x512.png',
        'favicon/web-app-manifest-144x144.png'
      ].map(function(url) {
        
        return cache.add(url).catch(function(error) {
          console.error('Failed to cache:', url, error);
        });
      }));
    })
  );
});
