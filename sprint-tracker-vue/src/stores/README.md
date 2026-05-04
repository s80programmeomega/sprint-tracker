# stores/

Pinia stores — global reactive state shared across the entire app.

Use a store when state needs to be accessed or mutated by multiple unrelated components (e.g. the authenticated user, the active project, a notification list).

Do NOT use a store for local component state (open/close a modal, a form input value) — use ref() inside the component instead.

Angular parallel: NgRx store or a singleton BehaviorSubject service.
Django parallel: session data or a global context object.

Examples:
- authStore.js    → current user, token, login/logout actions
- projectStore.js → list of projects, active project
- taskStore.js    → tasks for the current sprint, CRUD actions

Naming convention: camelCase with "Store" suffix.
