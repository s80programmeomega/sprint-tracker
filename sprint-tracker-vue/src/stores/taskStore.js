import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
    getTasks,
    createTask,
    updateTask,
    deleteTask,
} from '../api/tasks.js'

export const useTaskStore = defineStore('tasks', () => {

    // --- STATE ---
    // tasks: the list of tasks for the current sprint.
    // Initialized as empty array — populated by fetchTasks().
    const tasks = ref([])

    // loading: true while any async action is running.
    // Used in components to show a spinner or disable buttons.
    const loading = ref(false)

    // error: stores the last error message from a failed API call.
    // null when no error. Components can display this to the user.
    const error = ref(null)

    // --- GETTERS ---

    // These getters allow components to easily access tasks by status
    // without having to filter the list themselves. They will update
    // reactively whenever the tasks array changes.
    const todoTasks = computed(() =>
        tasks.value.filter(t => t.status === 'todo')
    )

    const inProgressTasks = computed(() =>
        tasks.value.filter(t => t.status === 'in_progress')
    )

    const doneTasks = computed(() =>
        tasks.value.filter(t => t.status === 'done')
    )

    const totalTasks = computed(() => tasks.value.length)

    // completionRate: the percentage of tasks that are done.
    // Rounded to nearest whole number. Returns 0 if there are
    // no tasks to avoid division by zero.
    const completionRate = computed(() => {
        if (tasks.value.length === 0) return 0
        return Math.round((doneTasks.value.length / tasks.value.length) * 100)
    })

    // --- ACTIONS ---

    //  load tasks for a given sprint ID.
    async function fetchTasks(sprintId) {
        loading.value = true
        error.value = null
        try {
            const data = await getTasks(sprintId)
            tasks.value = data.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to load tasks'
        } finally {
            loading.value = false
        }
    }

    // create a new task in the given sprint with the provided payload.
    async function addTask(sprintId, payload) {
        const data = await createTask(sprintId, payload)
        tasks.value.push(data.data)
        return data.data
    }

    // edit an existing task by ID with the provided payload.
    async function editTask(sprintId, taskId, payload) {
        const data = await updateTask(sprintId, taskId, payload)
        const index = tasks.value.findIndex(t => t.id === taskId)
        if (index !== -1) {
            tasks.value[index] = data.data
        }
        return data.data
    }

    // move a task to a new status (e.g. from 'todo' to 'in_progress')
    // by calling editTask with the new status in the payload. This is
    // a convenience method that abstracts away the details of how to
    //  update the task's status.
    async function moveTask(sprintId, taskId, newStatus) {
        return await editTask(sprintId, taskId, { status: newStatus })
    }

    // delete a task by ID and remove it from the tasks array.
    //  This method calls the deleteTask API function and then
    // filters out the deleted task from the local state to keep
    // the UI in sync without needing to re-fetch all tasks.
    async function removeTask(sprintId, taskId) {
        await deleteTask(sprintId, taskId)
        tasks.value = tasks.value.filter(t => t.id !== taskId)
    }

    // clearTasks: a utility method to reset the tasks state.
    // Called when leaving a sprint page to prevent stale tasks from
    // briefly appearing when navigating to a different sprint.

    function clearTasks() {
        tasks.value = []
        error.value = null
    }

    return {
        tasks,
        loading,
        error,
        todoTasks,
        inProgressTasks,
        doneTasks,
        totalTasks,
        completionRate,
        fetchTasks,
        addTask,
        editTask,
        moveTask,
        removeTask,
        clearTasks,
    }
})
