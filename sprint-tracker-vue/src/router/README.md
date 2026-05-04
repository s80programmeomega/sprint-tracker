# router/

Vue Router configuration — maps URLs to page components.

## Files
- index.js   → creates and exports the router instance, defines all routes

## Route structure
Each route maps a URL path to a page component from src/pages/.
Routes can have:
- meta fields (requiresAuth: true) — used by navigation guards
- nested children routes — for layouts with sub-pages
- lazy loading — components imported only when the route is visited

## Navigation guard
A global beforeEach guard is defined here (added in Phase 7).
It checks meta.requiresAuth and redirects unauthenticated users to /login.

Example route shape:
{
    path: '/projects',
    name: 'projects',
    component: () => import('../pages/ProjectsPage.vue'),  // lazy loaded
    meta: { requiresAuth: true }
}
