<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useTaskStore } from '../stores/taskStore.js'

const route = useRoute()
const taskStore = useTaskStore()

// Destructure the sprint ID once for readability.
// route.params are strings — we use them as-is since the API accepts both.
const sprintId = route.params.sprintId

onMounted(() => {
    taskStore.fetchTasks(sprintId)
})

// onUnmounted runs when the component is removed from the DOM (user navigates away).
// We clear the tasks so stale data from this sprint doesn't flash briefly
// when the user visits a different sprint.
onUnmounted(() => {
    taskStore.clearTasks()
})

// moveTask updates the task's status both on the server and in local state.
// The store handles both — the component just calls the action.
async function moveTask(taskId, newStatus) {
    await taskStore.moveTask(sprintId, taskId, newStatus)
}
</script>

<template>
    <div>
        <RouterLink :to="{ name: 'project-detail', params: { id: route.params.id } }">
            ← Back to project
        </RouterLink>

        <h1>Sprint Board</h1>

        <p v-if="taskStore.loading">Loading tasks…</p>
        <p v-if="taskStore.error" class="error">{{ taskStore.error }}</p>

        <!--
            The board is 3 columns — one per status.
            Each column reads from a store getter (todoTasks, inProgressTasks, doneTasks)
            which are computed() values that update automatically when tasks change.
        -->
        <div class="board" v-if="!taskStore.loading">
            <!-- TO DO column -->
            <div class="column">
                <h2>To Do ({{ taskStore.todoTasks.length }})</h2>
                <div v-for="task in taskStore.todoTasks" :key="task.id" class="task-card">
                    <p>{{ task.title }}</p>
                    <span class="priority">{{ task.priority }}</span>
                    <!-- Move to next status -->
                    <button @click="moveTask(task.id, 'in_progress')">→ In Progress</button>
                </div>
            </div>

            <!-- IN PROGRESS column -->
            <div class="column">
                <h2>In Progress ({{ taskStore.inProgressTasks.length }})</h2>
                <div v-for="task in taskStore.inProgressTasks" :key="task.id" class="task-card">
                    <p>{{ task.title }}</p>
                    <span class="priority">{{ task.priority }}</span>
                    <button @click="moveTask(task.id, 'todo')">← To Do</button>
                    <button @click="moveTask(task.id, 'done')">→ Done</button>
                </div>
            </div>

            <!-- DONE column -->
            <div class="column">
                <h2>Done ({{ taskStore.doneTasks.length }})</h2>
                <!--
                    completionRate is a store getter: (doneTasks / totalTasks) * 100
                    It recalculates automatically whenever tasks change.
                -->
                <p>{{ taskStore.completionRate }}% complete</p>
                <div v-for="task in taskStore.doneTasks" :key="task.id" class="task-card">
                    <p>{{ task.title }}</p>
                    <button @click="moveTask(task.id, 'in_progress')">← In Progress</button>
                </div>
            </div>
        </div>
    </div>
</template>
