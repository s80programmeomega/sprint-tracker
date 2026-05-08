<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore.js'

// useRouter() gives access to the router instance inside <script setup>.
// We use it to programmatically navigate after a successful login.
const router = useRouter()
const authStore = useAuthStore()

// Local form state — not in Pinia because no other component needs these values.
const email = ref('')
const password = ref('')
const error = ref(null)
const loading = ref(false)

async function submit() {
    error.value = null
    loading.value = true
    try {
        await authStore.handleLogin({ email: email.value, password: password.value })
        // Navigate to projects list after successful login.
        // { name: 'projects' } uses the route name instead of a hardcoded path —
        // safer because renaming the path won't break this navigation.
        router.push({ name: 'projects' })
    } catch (err) {
        // Laravel returns the error message under err.response.data.message.
        // The fallback string is shown if the server returned no message (e.g. network error).
        error.value = err.response?.data?.message || 'Login failed'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="auth-page">
        <h1>Sign in</h1>

        <!--
            @submit.prevent — .prevent calls event.preventDefault() so the browser
            doesn't do a full page reload on form submit. We handle it in JS instead.
        -->
        <form @submit.prevent="submit">
            <div>
                <label for="email">Email</label>
                <input id="email" v-model="email" type="email" required autocomplete="email" />
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" v-model="password" type="password" required autocomplete="current-password" />
            </div>

            <!-- v-if renders the element only when error is truthy -->
            <p v-if="error" class="error">{{ error }}</p>

            <!-- :disabled binds the disabled attribute dynamically -->
            <button type="submit" :disabled="loading">
                {{ loading ? 'Signing in…' : 'Sign in' }}
            </button>
        </form>

        <p>No account? <RouterLink :to="{ name: 'register' }">Register</RouterLink></p>
    </div>
</template>
