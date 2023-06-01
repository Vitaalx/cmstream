import CuteVue, {importer} from "../js/cuteVue/index.js";
import "./router/index.js";

const app = await importer("/public/cuteVue/app.html");

app.mount("#app");
