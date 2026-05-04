<script setup>
// RouterView is a named export from vue-router — must use curly braces.
// It acts as a dynamic slot: the router replaces it with whichever page
// component matches the current URL.
import { RouterView } from 'vue-router'
</script>

<template>
    <!--
        The entire app renders through this single component.
        When the URL changes, RouterView swaps the rendered page component.
        Global UI elements (navbar, sidebar, toast container) will be added here later.
    -->
    <RouterView />
</template>
