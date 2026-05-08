// Projects API calls — maps to Route::apiResource('projects') in routes/api.php.
// All routes require authentication (auth:sanctum middleware).
// The Authorization header is injected automatically by the Axios request interceptor.

import api from './axios.js'

// GET /api/projects — returns all projects the authenticated user is a member of.
export async function getProjects() {
    const response = await api.get('/projects')
    return response.data
}

// GET /api/projects/:id — returns a single project by ID.
export async function getProject(id) {
    const response = await api.get(`/projects/${id}`)
    return response.data
}

// POST /api/projects — creates a new project.
// data: { name, description? }
// The authenticated user is automatically set as owner and added as admin member.
export async function createProject(data) {
    const response = await api.post('/projects', data)
    return response.data
}

// PUT /api/projects/:id — updates an existing project.
// data: { name?, description?, status? } — only send fields you want to change.
export async function updateProject(id, data) {
    const response = await api.put(`/projects/${id}`, data)
    return response.data
}

// DELETE /api/projects/:id — deletes a project.
// Only the project owner with delete-project permission can do this.
export async function deleteProject(id) {
    const response = await api.delete(`/projects/${id}`)
    return response.data
}

// GET /api/projects/:id/sprints — returns all sprints for a project.
// Sprints are nested under projects in the API: Route::apiResource('projects.sprints')
export async function getSprints(projectId) {
    const response = await api.get(`/projects/${projectId}/sprints`)
    return response.data
}
