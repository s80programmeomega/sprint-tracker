// Tasks API calls — maps to Route::apiResource('sprints.tasks') in routes/api.php.
// Tasks are always nested under a sprint — every endpoint requires a sprintId.
// All routes require authentication (auth:sanctum middleware).

import api from './axios.js'

// GET /api/sprints/:sprintId/tasks — returns all tasks for a sprint.
export async function getTasks(sprintId) {
    const response = await api.get(`/sprints/${sprintId}/tasks`)
    return response.data
}

// POST /api/sprints/:sprintId/tasks — creates a new task inside a sprint.
// data: { title, description?, priority?, due_date?, assigned_to? }
// sprint_id is set automatically on the backend via the relationship.
export async function createTask(sprintId, data) {
    const response = await api.post(`/sprints/${sprintId}/tasks`, data)
    return response.data
}

// PUT /api/sprints/:sprintId/tasks/:taskId — updates a task.
// data: { title?, status?, priority?, due_date?, assigned_to? }
// Only send fields you want to change — backend uses 'sometimes' validation rules.
export async function updateTask(sprintId, taskId, data) {
    const response = await api.put(`/sprints/${sprintId}/tasks/${taskId}`, data)
    return response.data
}

// DELETE /api/sprints/:sprintId/tasks/:taskId — deletes a task.
// Requires delete-task permission (admin role only).
export async function deleteTask(sprintId, taskId) {
    const response = await api.delete(`/sprints/${sprintId}/tasks/${taskId}`)
    return response.data
}
