import { createRouter, createWebHistory } from 'vue-router'

// createWebHistory() uses the browser History API for clean URLs (/projects/1)
// instead of hash-based URLs (/#/projects/1).
// Requires the server to redirect all unknown paths to index.html in production.
const router = createRouter({
    history: createWebHistory(),

    // Routes are empty for now — added in Phase 7 when pages are built.
    // Each route maps a URL path to a page component.
    routes: [],
})

export default router
