# composables/

Reusable logic functions built with the Vue Composition API.

A composable is a plain JS function that starts with "use" and returns reactive state and/or methods. It encapsulates logic that multiple components need — without duplicating it.

Angular parallel: a service class with reactive state.
Django parallel: a utility module with shared helper functions.

Examples:
- useAuth.js      → login, logout, current user state
- useTasks.js     → fetch, create, update tasks for a sprint
- useProjects.js  → fetch and manage projects list

Naming convention: always prefix with "use" (Vue convention).
