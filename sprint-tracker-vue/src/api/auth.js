// Auth API calls — maps to the public and protected auth routes in routes/api.php.
// All functions use the shared Axios instance from axios.js which handles
// the Authorization header and 401 redirects automatically.

import api from './axios.js'

// POST /api/register — creates a new user account.
// data: { name, email, password, password_confirmation }
// returns: { user, token }
export async function register(data) {
    const response = await api.post('/register', data)
    return response.data
}

// POST /api/login — authenticates an existing user.
// data: { email, password }
// returns: { user, token }
export async function login(data) {
    const response = await api.post('/login', data)
    return response.data
}

// POST /api/logout — revokes the current access token on the server.
// No body needed — the token is sent automatically via the request interceptor.
export async function logout() {
    const response = await api.post('/logout')
    return response.data
}

// GET /api/me — returns the authenticated user with their roles.
// Used on app startup to restore the session from a stored token.
export async function me() {
    const response = await api.get('/me')
    return response.data
}
