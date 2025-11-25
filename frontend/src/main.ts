import { createApp } from "vue";
import { createPinia } from "pinia";

import App from "./App.vue";
import router from "./router";
import i18n from "./plugins/i18n";

import "./index.css";

import { useAuthStore } from "@/stores/auth";

const app = createApp(App);

const pinia = createPinia();

app.use(pinia);
app.use(router);
app.use(i18n);

const auth = useAuthStore(pinia);

auth.init().finally(() => {
    app.mount("#app");
});
