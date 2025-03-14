self.addEventListener("install", (event) => {
    console.log("Service Worker installing...");
    event.waitUntil(
      self.skipWaiting().then(() => {
        console.log("Service Worker installed and skipWaiting called.");
      }).catch((error) => {
        console.error("Error during Service Worker installation:", error);
      })
    );
  });
  
  self.addEventListener("activate", (event) => {
    console.log("Service Worker activated!");
    event.waitUntil(
      self.clients.claim().then(() => {
        console.log("Service Worker claimed clients.");
      }).catch((error) => {
        console.error("Error during Service Worker activation:", error);
      })
    );
  });
  
  self.addEventListener("fetch", (event) => {
    console.log("Intercepting request for:", event.request.url);
    event.respondWith(
      fetch(event.request).catch((error) => {
        console.error("Fetch failed for:", event.request.url, "Error:", error);
      })
    );
  });