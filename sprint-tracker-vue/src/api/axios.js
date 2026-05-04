import axios from 'axios'

// The single Axios instance used by every API call in the app.
// All files in src/api/ import from here — never create a new axios instance elsewhere.
const api = axios.create({
    baseURL: 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

// Request interceptor — runs before every request is sent.
// Reads the token from localStorage and injects it into the Authorization header.
// This is why you never manually add the token in individual API calls.
api.interceptors.request.use(config => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

// Response interceptor — runs after every response is received.
// If the server returns 401 (token expired or invalid), clear local storage
// and redirect to login so the user is not stuck in a broken state.
api.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default api
