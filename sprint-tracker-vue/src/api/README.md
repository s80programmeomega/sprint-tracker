# api/

Axios API call functions grouped by domain.

Each file handles all HTTP calls for one resource. No business logic here — just the raw request and the returned data. Stores and composables call these functions.

Django parallel: the API client layer (equivalent to calling requests.get() in a service).

Examples:
- auth.js      → login(), register(), logout(), me()
- projects.js  → getProjects(), createProject(), updateProject(), deleteProject()
- sprints.js   → getSprints(), createSprint(), updateSprint()
- tasks.js     → getTasks(), createTask(), updateTask(), deleteTask()

All files import the shared Axios instance from axios.js (also in this folder),
which has the base URL and Authorization header pre-configured.
