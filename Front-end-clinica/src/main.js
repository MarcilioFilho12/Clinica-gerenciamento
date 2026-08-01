import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from "./router"
import { createPinia } from 'pinia';
import Toast from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { vMaska } from "maska/vue";
import registerGlobalComponents from './components/globals.js'

const app = createApp(App) 
const pinia = createPinia();

app.directive('maska', vMaska)
registerGlobalComponents(app)

app.use(router);
app.use(pinia);
app.use(Toast, {
    autoClose: 2000,
    position: "top-right",
    transition: "slide"
});
app.mount("#app");

