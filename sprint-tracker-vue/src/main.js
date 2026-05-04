import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router/index.js'
import App from './App.vue'

// Create the Vue application instance with App.vue as the root component.
// Every component, store, and route lives inside this instance.
const app = createApp(App)

// Register Pinia — the state management plugin.
// After this, any component can access any store via useXxxStore().
app.use(createPinia())

// Register Vue Router — enables <RouterView>, <RouterLink>, useRouter(), useRoute().
app.use(router)

// Mount the app into the DOM element with id="app" (defined in index.html).
// This must always be the last call — nothing renders before this line.
app.mount('#app')
