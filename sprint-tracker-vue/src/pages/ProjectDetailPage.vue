<script setup>
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useProjectStore } from '../stores/projectStore.js'
import { getSprints } from '../api/projects.js'
import { ref } from 'vue'

// useRoute() gives access to the current route's params, query, name, etc.
// It's the read-only counterpart of useRouter() (which is for navigation).
const route = useRoute()
const projectStore = useProjectStore()

const sprints = ref([])
const loadingSprints = ref(false)

onMounted(async () => {
    // route.params.id is the :id segment from /projects/:id
    // It's always a string — coerce to number if your API expects one.
    await projectStore.fetchProject(route.params.id)
    await fetchSprints()
})

async function fetchSprints() {
    loadingSprints.value = true
    try {
        const data = await getSprints(route.params.id)
        sprints.value = data.data
    } finally {
        loadingSprints.value = false
    }
}
</script>

<template>
    <div>
        <RouterLink :to="{ name: 'projects' }">← Back to projects</RouterLink>

        <div v-if="projectStore.loading">Loading…</div>

        <template v-else-if="projectStore.currentProject">
            <h1>{{ projectStore.currentProject.name }}</h1>
            <p v-if="projectStore.currentProject.description">
                {{ projectStore.currentProject.description }}
            </p>

            <h2>Sprints</h2>

            <p v-if="loadingSprints">Loading sprints…</p>

            <ul v-else-if="sprints.length">
                <li v-for="sprint in sprints" :key="sprint.id">
                    <!--
                        RouterLink to the sprint board — passes both :id (project) and
                        :sprintId as route params to match /projects/:id/sprints/:sprintId
                    -->
                    <RouterLink :to="{ name: 'sprint-board', params: { id: route.params.id, sprintId: sprint.id } }">
                        {{ sprint.name }}
                    </RouterLink>
                    <span> ({{ sprint.status }})</span>
                    <span> {{ sprint.start_date }} → {{ sprint.end_date }}</span>
                </li>
            </ul>

            <p v-else>No sprints yet.</p>
        </template>
    </div>
</template>
