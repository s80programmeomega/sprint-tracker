<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from '../stores/projectStore.js'
import { useAuthStore } from '../stores/authStore.js'

const router = useRouter()
const projectStore = useProjectStore()
const authStore = useAuthStore()

// Local state for the "create project" form visibility and input.
// This is UI state — it belongs in the component, not the store.
const showForm = ref(false)
const newProjectName = ref('')
const newProjectDescription = ref('')

// onMounted runs after the component is inserted into the DOM.
// This is the standard place to trigger initial data fetching.
// projectStore.loading and projectStore.error are updated inside fetchProjects().
onMounted(() => {
    projectStore.fetchProjects()
})

async function createProject() {
    if (!newProjectName.value.trim()) return
    await projectStore.addProject({
        name: newProjectName.value,
        description: newProjectDescription.value,
    })
    // Reset form and hide it after successful creation.
    newProjectName.value = ''
    newProjectDescription.value = ''
    showForm.value = false
}

async function logout() {
    await authStore.handleLogout()
    router.push({ name: 'login' })
}
</script>

<template>
    <div>
        <header>
            <h1>Projects</h1>
            <div>
                <span>{{ authStore.user?.name }}</span>
                <button @click="logout">Logout</button>
            </div>
        </header>

        <!-- Loading and error states from the store -->
        <p v-if="projectStore.loading">Loading…</p>
        <p v-else-if="projectStore.error" class="error">{{ projectStore.error }}</p>

        <ul v-else>
            <!--
                v-for renders a list item for each project.
                :key is required — Vue uses it to efficiently update the DOM
                when the list changes. Always use a unique, stable ID, not the index.
            -->
            <li v-for="project in projectStore.projects" :key="project.id">
                <!--
                    RouterLink renders an <a> tag that navigates without a full page reload.
                    :to binds the destination dynamically — params.id sets the :id segment.
                -->
                <RouterLink :to="{ name: 'project-detail', params: { id: project.id } }">
                    {{ project.name }}
                </RouterLink>
                <span v-if="project.description">— {{ project.description }}</span>
            </li>
        </ul>

        <button @click="showForm = !showForm">
            {{ showForm ? 'Cancel' : 'New project' }}
        </button>

        <!-- v-if completely removes the form from the DOM when false -->
        <form v-if="showForm" @submit.prevent="createProject">
            <input v-model="newProjectName" placeholder="Project name" required />
            <input v-model="newProjectDescription" placeholder="Description (optional)" />
            <button type="submit">Create</button>
        </form>
    </div>
</template>
