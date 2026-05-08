import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login, register, logout, me } from '../api/auth.js'

// defineStore('auth', ...) — 'auth' is the unique store ID used by Pinia internally.
// The second argument is a function (Composition API style) — same syntax as <script setup>.
export const useAuthStore = defineStore('auth', () => {

    // --- STATE ---
    // Initialized from localStorage so the session survives a page refresh.
    // If nothing is stored yet, defaults to null.

    // JSON.parse() converts the stored JSON string back to a JS object.
    // The || null fallback handles the case where the key doesn't exist in localStorage.
    const user = ref(JSON.parse(localStorage.getItem('user')) || null)

    // Token stored as a plain string — no JSON parsing needed.
    const token = ref(localStorage.getItem('token') || null)

    // --- GETTERS ---
    // computed() recalculates automatically when its dependencies (token, user) change.

    // !! converts a value to boolean: null → false, 'abc' → true.
    // isAuthenticated is true when a token exists, false when logged out.
    const isAuthenticated = computed(() => !!token.value)

    // Optional chaining (?.) safely traverses nested properties.
    // user.value?.roles?.[0]?.name returns null instead of crashing
    // if user is null, roles is empty, or the role has no name.
    const userRole = computed(() => user.value?.roles?.[0]?.name || null)

    // --- ACTIONS ---
    // Each action: call the API, update reactive state, sync localStorage.
    // Keeping state and localStorage in sync ensures the session survives refresh.

    async function handleLogin(credentials) {
        // credentials: { email, password }
        const data = await login(credentials)   // POST /api/login
        user.value = data.user
        token.value = data.token
        // Persist to localStorage so the Axios interceptor can read the token
        // on subsequent requests, even after a page refresh.
        localStorage.setItem('user', JSON.stringify(data.user))
        localStorage.setItem('token', data.token)
    }

    async function handleRegister(credentials) {
        // credentials: { name, email, password, password_confirmation }
        const data = await register(credentials)    // POST /api/register
        user.value = data.user
        token.value = data.token
        localStorage.setItem('user', JSON.stringify(data.user))
        localStorage.setItem('token', data.token)
    }

    async function handleLogout() {
        // First revoke the token on the server, then clear local state.
        // Order matters: if we clear first and the request fails, the server
        // still has a valid token floating around.
        await logout()                          // POST /api/logout
        user.value = null
        token.value = null
        localStorage.removeItem('user')
        localStorage.removeItem('token')
    }

    async function fetchCurrentUser() {
        // Called on app startup when a token exists in localStorage.
        // Re-fetches the user from the server to get fresh data (roles, avatar, etc.)
        // in case it changed since the last session.
        const data = await me()                 // GET /api/me
        user.value = data
        localStorage.setItem('user', JSON.stringify(data))
    }

    // Everything returned here is accessible from any component via useAuthStore().
    // Only expose what components need — internal helpers can stay private.
    return {
        user,
        token,
        isAuthenticated,
        userRole,
        handleLogin,
        handleRegister,
        handleLogout,
        fetchCurrentUser,
    }
})
