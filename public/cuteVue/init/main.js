import CuteVue, {importer} from "../../js/cuteVue/index.js";
import "../stores/loader.js";

const [view, cv_form, text_input, page_loader] = await Promise.all([
    importer("/public/cuteVue/init/view.html"),
    importer("/public/cuteVue/components/cv-form.html"),
    importer("/public/cuteVue/components/inputs/text-input.html"),
    importer("/public/cuteVue/components/page-loader.html"),
]);

CuteVue.component("cv-form", cv_form);
CuteVue.component("text-input", text_input);
CuteVue.component("page-loader", page_loader);

view.mount("#app");