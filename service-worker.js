self.addEventListener('install', (event) => {
	const scopeUrl = new URL(self.registration.scope)
	const appBasePath = scopeUrl.pathname.endsWith('/')
		? scopeUrl.pathname
		: `${scopeUrl.pathname}/`
	const cacheName = `shiftsnext-shell-v1`
	const shellAssets = [
		`${appBasePath}js/shiftsnext-mainApp.mjs`,
		`${appBasePath}css/shiftsnext-mainApp.css`,
	]
	event.waitUntil(
		caches.open(cacheName).then((cache) => cache.addAll(shellAssets).catch(() => undefined)),
	)
	self.skipWaiting()
})

self.addEventListener('activate', (event) => {
	const cacheName = `shiftsnext-shell-v1`
	event.waitUntil(
		caches.keys().then((keys) => Promise.all(
			keys
				.filter((key) => key.startsWith('shiftsnext-shell-') && key !== cacheName)
				.map((key) => caches.delete(key)),
		)),
	)
	self.clients.claim()
})

self.addEventListener('fetch', (event) => {
	const request = event.request
	const url = new URL(request.url)
	const isSameOrigin = url.origin === self.location.origin

	if (!isSameOrigin || request.method !== 'GET') {
		return
	}

	if (request.mode === 'navigate') {
		event.respondWith(
			fetch(request).catch(() => {
				return new Response(
					'<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shifts Next</title></head><body><main style="font-family:sans-serif;padding:1rem"><h1>Shifts Next</h1><p>You are offline. Please reconnect to load shift data.</p></main></body></html>',
					{
						headers: { 'Content-Type': 'text/html; charset=utf-8' },
					},
				)
			}),
		)
		return
	}

	if (request.destination === 'script' || request.destination === 'style') {
		event.respondWith(
			caches.match(request).then((cached) => {
				if (cached) {
					return cached
				}
				return fetch(request).then((response) => {
					const responseToCache = response.clone()
					caches.open('shiftsnext-shell-v1').then((cache) => {
						cache.put(request, responseToCache).catch(() => undefined)
					})
					return response
				})
			}),
		)
	}
})
