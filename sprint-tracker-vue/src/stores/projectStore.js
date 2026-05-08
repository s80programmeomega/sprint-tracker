import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
    getProjects,
    getProject,
    createProject,
    updateProject,
    deleteProject,
} from '../api/projects.js'

export const useProjectStore = defineStore('projects', () => {

    // --- STATE ---
    // projects: the full list of projects the authenticated user belongs to.
    // Initialized as empty array — populated by fetchProjects().
    const projects = ref([])

    // currentProject: the single project currently being viewed (detail page).
    // Separate from the list so navigating to a project doesn't require
    // searching through the projects array.
    const currentProject = ref(null)

    // loading: true while any async action is running.
    // Used in components to show a spinner or disable buttons.
    const loading = ref(false)

    // error: stores the last error message from a failed API call.
    // null when no error. Components can display this to the user.
    const error = ref(null)

    // --- GETTERS ---
    // Derived from state — recalculate automatically when projects.value changes.

    // Returns only projects with status 'active' — used on the dashboard.
    const activeProjects = computed(() =>
        projects.value.filter(p => p.status === 'active')
    )

    // Returns the total count — used in stats/badges.
    const totalProjects = computed(() => projects.value.length)

    // --- ACTIONS ---
    // Pattern used in fetchProjects and fetchProject:
    //   1. Set loading = true and clear previous error
    //   2. Try the API call and update state on success
    //   3. Catch any error and store the message
    //   4. Always set loading = false in finally (runs whether success or failure)

    async function fetchProjects() {
        loading.value = true
        error.value = null
        try {
            const data = await getProjects()
            // Laravel API Resources wrap the payload in a 'data' key:
            // { data: [...projects] } — so we read data.data, not just data.
            projects.value = data.data
        } catch (err) {
            // err.response?.data?.message — the error message from Laravel's JSON response.
            // The fallback string is shown if the server returned no message (e.g. network error).
            error.value = err.response?.data?.message || 'Failed to load projects'
        } finally {
            loading.value = false
        }
    }

    async function fetchProject(id) {
        loading.value = true
        error.value = null
        try {
            const data = await getProject(id)
            currentProject.value = data.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load project'
        } finally {
            loading.value = false
        }
    }

    async function addProject(payload) {
        const data = await createProject(payload)
        // Push the new project directly into the list instead of re-fetching all projects.
        // This avoids an unnecessary API call and keeps the UI in sync instantly.
        projects.value.push(data.data)
        return data.data
    }

    async function editProject(id, payload) {
        const data = await updateProject(id, payload)
        // Find the index of the updated project in the array and replace it in place.
        // findIndex returns -1 if not found — the guard prevents an out-of-bounds assignment.
        const index = projects.value.findIndex(p => p.id === id)
        if (index !== -1) {
            projects.value[index] = data.data
        }
        return data.data
    }

    async function removeProject(id) {
        await deleteProject(id)
        // Filter out the deleted project locally instead of re-fetching.
        // filter() returns a new array — assigning it triggers Vue's reactivity.
        projects.value = projects.value.filter(p => p.id !== id)
    }

    // Expose everything components need.
    // State and getters are read-only from components — only actions mutate them.
    return {
        projects,
        currentProject,
        loading,
        error,
        activeProjects,
        totalProjects,
        fetchProjects,
        fetchProject,
        addProject,
        editProject,
        removeProject,
    }
})
