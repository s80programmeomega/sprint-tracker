import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/authStore.js'

// createWebHistory() uses the browser History API for clean URLs (/projects/1)
// instead of hash-based URLs (/#/projects/1).
// Requires the server to redirect all unknown paths to index.html in production.
const router = createRouter({
    history: createWebHistory(),

    // Each route maps a URL path to a page component.
    // () => import(...) is lazy loading — the component's JS is only downloaded
    // when the user visits that route, not on initial app load. Faster startup.
    //
    // meta.requiresAuth: true is a custom flag we read in the navigation guard below
    // to decide whether to redirect unauthenticated users to /login.
    routes: [
        // --- Public routes (no auth required) ---
        {
            path: '/login',
            name: 'login',
            component: () => import('../pages/LoginPage.vue'),
        },
        {
            path: '/register',
            name: 'register',
            component: () => import('../pages/RegisterPage.vue'),
        },

        // --- Protected routes ---
        {
            path: '/projects',
            name: 'projects',
            component: () => import('../pages/ProjectsPage.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/projects/:id',
            name: 'project-detail',
            component: () => import('../pages/ProjectDetailPage.vue'),
            meta: { requiresAuth: true },
        },
        {
            // :id = project ID, :sprintId = sprint ID
            // Both are available in the page via useRoute().params
            path: '/projects/:id/sprints/:sprintId',
            name: 'sprint-board',
            component: () => import('../pages/SprintBoardPage.vue'),
            meta: { requiresAuth: true },
        },

        // Catch-all: redirect any unknown URL to /projects.
        // /:pathMatch(.*)*  is Vue Router's syntax for "match everything".
        {
            path: '/:pathMatch(.*)*',
            redirect: { name: 'projects' },
        },
    ],
})

// --- Navigation guard ---
// beforeEach runs before EVERY route change, including the initial page load.
// to   = the route the user is navigating TO
// from = the route they are coming FROM (unused here)
//
// Return values:
//   { name: 'login' } → redirect to that route instead
//   undefined (nothing) → proceed with the original navigation
router.beforeEach((to) => {
    // useAuthStore() must be called INSIDE the guard, not at the top of the file.
    // Pinia isn't available until after app.use(createPinia()) runs in main.js.
    const authStore = useAuthStore()

    // If the route requires auth and the user is not logged in → redirect to /login.
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return { name: 'login' }
    }

    // If already logged in and trying to visit /login or /register → redirect to /projects.
    // No point showing auth forms to a logged-in user.
    if ((to.name === 'login' || to.name === 'register') && authStore.isAuthenticated) {
        return { name: 'projects' }
    }
})

export default router
