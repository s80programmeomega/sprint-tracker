<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authStore.js'

const router = useRouter()
const authStore = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const error = ref(null)
const loading = ref(false)

async function submit() {
    error.value = null
    loading.value = true
    try {
        await authStore.handleRegister({
            name: name.value,
            email: email.value,
            password: password.value,
            // Laravel's 'confirmed' rule expects the field named password_confirmation.
            password_confirmation: passwordConfirmation.value,
        })
        router.push({ name: 'projects' })
    } catch (err) {
        // Laravel returns 422 with validation errors as an object under .errors.
        // We join all messages into a single string for simplicity.
        const errors = err.response?.data?.errors
        error.value = errors
            ? Object.values(errors).flat().join(' ')
            : err.response?.data?.message || 'Registration failed'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="auth-page">
        <h1>Create account</h1>

        <form @submit.prevent="submit">
            <div>
                <label for="name">Name</label>
                <input id="name" v-model="name" type="text" required autocomplete="name" />
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" v-model="email" type="email" required autocomplete="email" />
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" v-model="password" type="password" required autocomplete="new-password" />
            </div>

            <div>
                <label for="password-confirmation">Confirm password</label>
                <input id="password-confirmation" v-model="passwordConfirmation" type="password" required autocomplete="new-password" />
            </div>

            <p v-if="error" class="error">{{ error }}</p>

            <button type="submit" :disabled="loading">
                {{ loading ? 'Creating account…' : 'Register' }}
            </button>
        </form>

        <p>Already have an account? <RouterLink :to="{ name: 'login' }">Sign in</RouterLink></p>
    </div>
</template>
