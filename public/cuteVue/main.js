import CuteVue, {importer} from "../js/cuteVue/index.js";

let test = await importer("/public/cuteVue/components/test.html");

test.mount("#app");
